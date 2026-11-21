<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\DeliveryShare;
use App\Models\Order;
use App\Models\Product;
use App\Models\RecurringOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_saved_address_is_snapshotted_to_checkout_order(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $address = Address::create([
            'user_id' => $buyer->id,
            'label' => 'Home',
            'recipient_name' => 'Amina Namusoke',
            'phone' => '+256700123456',
            'district' => 'Wakiso',
            'subcounty' => 'Kira',
            'village' => 'Bweyogerere',
            'instructions' => 'Call at the gate',
            'is_default' => true,
        ]);
        $product = $this->createProduct($farmer);

        $this->actingAs($buyer)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), ['address_id' => $address->id])
            ->assertRedirect(route('orders.index'));

        $order = Order::firstOrFail();
        $this->assertSame('Amina Namusoke', $order->recipient_name);
        $this->assertSame('+256700123456', $order->delivery_phone);
        $this->assertStringContainsString('Bweyogerere', $order->delivery_address);
        $this->assertSame('Call at the gate', $order->delivery_instructions);

        $otherAddress = Address::create([
            'user_id' => User::factory()->consumer()->create()->id,
            'label' => 'Other home',
            'recipient_name' => 'Other buyer',
            'phone' => '+256700987654',
            'district' => 'Kampala',
            'subcounty' => 'Central',
            'village' => 'Nakasero',
            'is_default' => true,
        ]);
        $this->post(route('checkout.store'), ['address_id' => $otherAddress->id])
            ->assertSessionHasErrors('address_id');
    }

    public function test_seller_can_advance_delivery_and_buyer_can_review_delivered_item(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer);
        $order = $this->placeOrder($farmer, $buyer, $product);

        $this->actingAs($farmer)->patch(route('seller.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect(route('seller.orders'));
        $this->patch(route('seller.orders.delivery', $order), [
            'delivery_status' => 'scheduled',
            'estimated_delivery_at' => now()->addHour()->toDateTimeString(),
        ])->assertRedirect(route('seller.orders'));
        $this->patch(route('seller.orders.delivery', $order), ['delivery_status' => 'in_transit'])
            ->assertRedirect(route('seller.orders'));
        $this->patch(route('seller.orders.delivery', $order), ['delivery_status' => 'delivered'])
            ->assertRedirect(route('seller.orders'));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
            'delivery_status' => 'delivered',
        ]);
        $this->assertNotNull($order->fresh()->confirmed_at);
        $this->assertNotNull($order->fresh()->delivery_scheduled_at);
        $this->assertNotNull($order->fresh()->in_transit_at);
        $this->assertNotNull($order->fresh()->delivered_at);

        $item = $order->items()->firstOrFail();
        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Delivery tracking')
            ->assertSee('Delivered');
        $this->post(route('marketplace.reviews.store', $product), [
            'order_item_id' => $item->id,
            'rating' => 5,
            'body' => 'Fresh and carefully packed.',
        ])->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'order_item_id' => $item->id,
            'buyer_id' => $buyer->id,
            'rating' => 5,
        ]);
    }

    public function test_order_messages_are_limited_to_the_buyer_and_seller(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $stranger = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer);
        $order = $this->placeOrder($farmer, $buyer, $product);

        $this->actingAs($buyer)->post(route('messages.store', $order), ['body' => 'Please call before delivery.'])
            ->assertRedirect(route('messages.show', $order));
        $this->actingAs($farmer)->get(route('messages.show', $order))
            ->assertOk()
            ->assertSee('Please call before delivery.');
        $this->actingAs($stranger)->get(route('messages.show', $order))->assertNotFound();
        $this->post(route('messages.store', $order), ['body' => 'Thanks.'])->assertNotFound();
    }

    public function test_recurring_supplier_schedule_creates_due_order_without_charging(): void
    {
        $supplier = User::factory()->supplier()->create();
        $buyer = User::factory()->consumer()->create();
        $product = $this->createProduct($supplier, [
            'category' => 'feed',
            'name' => 'Layer mash',
            'price' => 85000,
            'quantity' => 8,
            'unit' => 'bag',
        ]);
        RecurringOrder::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $supplier->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'frequency' => 'monthly',
            'next_run_at' => now()->subMinute(),
            'status' => 'active',
        ]);

        $this->artisan('marketplace:process-recurring-orders')->assertExitCode(0);

        $order = Order::firstOrFail();
        $this->assertSame($buyer->id, $order->buyer_id);
        $this->assertSame(170000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame(6, $product->fresh()->quantity);
        $this->assertGreaterThan(now(), RecurringOrder::firstOrFail()->next_run_at);
    }

    public function test_low_stock_alert_is_sent_and_cleared_after_restock(): void
    {
        $farmer = User::factory()->farmer()->create();
        $product = $this->createProduct($farmer, [
            'name' => 'Free-range eggs',
            'unit' => 'tray',
            'quantity' => 2,
            'low_stock_threshold' => 3,
        ]);
        $this->artisan('marketplace:send-inventory-alerts')->assertExitCode(0);

        $this->actingAs($farmer)->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Low stock');
        $this->assertSame(1, $farmer->unreadNotifications()->count());

        $this->put(route('seller.products.update', $product), [
            'category' => 'poultry',
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 10,
            'low_stock_threshold' => 3,
            'unit' => 'tray',
            'status' => 'active',
        ])->assertRedirect(route('seller.products.index'));
        $this->assertSame(0, $farmer->unreadNotifications()->count());

        $this->put(route('seller.products.update', $product), [
            'category' => 'poultry',
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 2,
            'low_stock_threshold' => 3,
            'unit' => 'tray',
            'status' => 'active',
        ])->assertRedirect(route('seller.products.index'));
        $this->assertSame(1, $farmer->unreadNotifications()->count());
    }

    public function test_expired_products_are_not_available_through_marketplace_or_cart(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer, ['expires_at' => today()->subDay()]);

        $this->actingAs($buyer)->get(route('marketplace.index'))
            ->assertOk()
            ->assertDontSee($product->name);
        $this->get(route('marketplace.show', $product))->assertNotFound();
        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertSessionHas('error');
    }

    public function test_seller_analytics_filters_completed_revenue_to_selected_period(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $recentProduct = $this->createProduct($farmer, ['name' => 'Recent eggs', 'price' => 11000]);
        $olderProduct = $this->createProduct($farmer, ['name' => 'Older feed', 'category' => 'feed', 'price' => 99000, 'unit' => 'bag']);

        foreach ([[$recentProduct, now()->subDays(3)], [$olderProduct, now()->subDays(20)]] as [$product, $completedAt]) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $farmer->id,
                'order_number' => 'PH-'.uniqid(),
                'total' => $product->price,
                'status' => 'completed',
                'completed_at' => $completedAt,
            ]);
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category' => $product->category,
                'unit' => $product->unit,
                'quantity' => 1,
                'unit_price' => $product->price,
                'total' => $product->price,
            ]);
        }

        $this->actingAs($farmer)->get(route('seller.analytics', ['days' => 7]))
            ->assertOk()
            ->assertSee('UGX 11,000')
            ->assertDontSee('UGX 99,000');
        $this->get(route('seller.analytics', ['days' => 30]))
            ->assertOk()
            ->assertSee('UGX 110,000');
    }

    public function test_farm_map_shows_only_sellers_who_opt_in_to_public_location(): void
    {
        $visibleFarmer = User::factory()->farmer()->create([
            'name' => 'Visible Nakuru Farm',
            'latitude' => -0.3031,
            'longitude' => 36.0800,
            'map_visible' => true,
        ]);
        $privateSupplier = User::factory()->supplier()->create([
            'name' => 'Private Feed Shop',
            'latitude' => 0.3476,
            'longitude' => 32.5825,
            'map_visible' => false,
        ]);

        $this->actingAs(User::factory()->consumer()->create())
            ->get(route('marketplace.map'))
            ->assertOk()
            ->assertSee('Visible Nakuru Farm')
            ->assertDontSee('Private Feed Shop');

        $this->actingAs($privateSupplier)->put(route('seller.location.update'), [
            'latitude' => 0.3476,
            'longitude' => 32.5825,
            'map_visible' => '1',
        ])->assertRedirect(route('marketplace.map'));
        $this->assertTrue($privateSupplier->fresh()->map_visible);

        $this->put(route('seller.location.update'), [
            'latitude' => 0.3476,
            'longitude' => 32.5825,
        ])->assertRedirect(route('marketplace.map'));
        $this->assertFalse($privateSupplier->fresh()->map_visible);

        $this->actingAs($visibleFarmer)->put(route('seller.location.update'), [
            'latitude' => -91,
            'longitude' => 36.08,
            'map_visible' => '1',
        ])->assertSessionHasErrors('latitude');
    }

    public function test_private_rider_link_shares_gps_only_with_order_participants_and_is_revoked_on_delivery(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $stranger = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer);
        $order = $this->placeOrder($farmer, $buyer, $product);
        $order->update(['status' => 'confirmed', 'delivery_status' => 'in_transit']);

        $response = $this->actingAs($farmer)->post(route('seller.orders.delivery-share', $order))
            ->assertRedirect(route('seller.orders'))
            ->assertSessionHas('delivery_share_url');
        $shareUrl = $response->getSession()->get('delivery_share_url');
        $token = parse_url($shareUrl, PHP_URL_FRAGMENT);
        $share = DeliveryShare::firstOrFail();

        $this->assertSame(hash('sha256', $token), $share->token_hash);
        $this->assertNotSame($token, $share->token_hash);

        $this->get(route('delivery-share.show'))
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer');
        auth('web')->logout();
        $this->postJson(route('delivery-share.location'), [
            'token' => $token,
            'latitude' => 0.3476,
            'longitude' => 32.5825,
            'accuracy_meters' => 12,
        ])->assertOk()->assertJsonPath('location.latitude', 0.3476);

        $this->actingAs($buyer)->getJson(route('orders.delivery-location', $order))
            ->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('location.longitude', 32.5825);
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('data-order-delivery-map', false);
        $this->actingAs($stranger)->getJson(route('orders.delivery-location', $order))->assertNotFound();

        $this->actingAs($farmer)->patch(route('seller.orders.delivery', $order), ['delivery_status' => 'delivered'])
            ->assertRedirect(route('seller.orders'));
        $this->assertDatabaseHas('delivery_shares', [
            'id' => $share->id,
            'latitude' => null,
            'longitude' => null,
        ]);
        $this->assertNotNull($share->fresh()->revoked_at);
        $this->postJson(route('delivery-share.location'), ['token' => $token])
            ->assertStatus(410);
    }

    public function test_seller_can_revoke_a_rider_link_before_delivery_finishes(): void
    {
        $farmer = User::factory()->farmer()->create();
        $buyer = User::factory()->consumer()->create();
        $order = $this->placeOrder($farmer, $buyer, $this->createProduct($farmer));
        $order->update(['status' => 'confirmed', 'delivery_status' => 'in_transit']);

        $this->actingAs($farmer)->post(route('seller.orders.delivery-share', $order));
        $share = DeliveryShare::firstOrFail();
        $share->update([
            'latitude' => 0.3476,
            'longitude' => 32.5825,
            'location_updated_at' => now(),
        ]);

        $this->delete(route('seller.orders.delivery-share.revoke', $order))
            ->assertRedirect(route('seller.orders'))
            ->assertSessionHas('success');
        $this->assertNotNull($share->fresh()->revoked_at);
        $this->assertNull($share->fresh()->latitude);
        $this->assertNull($share->fresh()->longitude);
    }

    private function placeOrder(User $farmer, User $buyer, Product $product): Order
    {
        $this->actingAs($buyer)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'));

        return Order::latest('id')->firstOrFail();
    }

    private function createProduct(User $seller, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'seller_id' => $seller->id,
            'category' => 'poultry',
            'name' => 'Free-range chicken',
            'price' => 25000,
            'quantity' => 10,
            'low_stock_threshold' => 5,
            'unit' => 'bird',
            'status' => 'active',
        ], $attributes));
    }
}
