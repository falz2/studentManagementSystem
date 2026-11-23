<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoultryHubCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_consumer_can_place_an_order_and_checkout_updates_inventory(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer, ['quantity' => 5]);

        $this->actingAs($consumer)->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));
        $this->post(route('checkout.store'), ['notes' => 'Please call on arrival.'])
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('success');

        $order = Order::with('items')->firstOrFail();
        $this->assertSame($consumer->id, $order->buyer_id);
        $this->assertSame($farmer->id, $order->seller_id);
        $this->assertSame(50000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(3, $product->fresh()->quantity);
        $this->assertSame([], session('cart', []));
    }

    public function test_checkout_splits_orders_between_sellers(): void
    {
        $farmer = User::factory()->farmer()->create();
        $supplier = User::factory()->supplier()->create();
        $consumer = User::factory()->consumer()->create();
        $chicken = $this->createProduct($farmer, ['name' => 'Chicken', 'price' => 25000]);
        $feed = $this->createProduct($supplier, ['name' => 'Feed', 'category' => 'feed', 'price' => 85000, 'unit' => 'bag']);

        $this->actingAs($consumer)->post(route('cart.add', $chicken), ['quantity' => 1]);
        $this->post(route('cart.add', $feed), ['quantity' => 1]);
        $this->post(route('checkout.store'));

        $this->assertSame(2, Order::count());
        $this->assertDatabaseHas('orders', ['seller_id' => $farmer->id, 'total' => 25000]);
        $this->assertDatabaseHas('orders', ['seller_id' => $supplier->id, 'total' => 85000]);
    }

    public function test_checkout_rejects_stale_stock_without_creating_an_order(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer, ['quantity' => 4]);

        $this->actingAs($consumer)->post(route('cart.add', $product), ['quantity' => 3]);
        $product->update(['quantity' => 1]);
        $this->post(route('checkout.store'))
            ->assertSessionHasErrors('cart')
            ->assertSessionHas('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->quantity);
    }

    public function test_buyer_can_cancel_pending_order_and_restore_inventory(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer, ['quantity' => 4]);
        $this->actingAs($consumer)->post(route('cart.add', $product), ['quantity' => 2]);
        $this->post(route('checkout.store'));
        $order = Order::firstOrFail();
        $this->assertSame(2, $product->fresh()->quantity);

        $this->patch(route('orders.cancel', $order))
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertSame(4, $product->fresh()->quantity);
    }

    public function test_cart_indicates_when_available_stock_has_changed(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer, ['quantity' => 4]);
        $this->actingAs($consumer)->post(route('cart.add', $product), ['quantity' => 3]);
        $product->update(['quantity' => 1]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Only 1 bird remain')
            ->assertSee('Update the quantity to continue.')
            ->assertSee('disabled', false);
    }

    public function test_only_the_seller_can_advance_the_order_status(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = $this->createProduct($farmer);
        $this->actingAs($consumer)->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'));
        $order = Order::firstOrFail();

        $this->actingAs($consumer)->patch(route('seller.orders.update', $order), ['status' => 'confirmed'])->assertForbidden();
        $this->actingAs($farmer)->patch(route('seller.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect(route('seller.orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);

        $this->patch(route('seller.orders.update', $order), ['status' => 'completed'])
            ->assertRedirect(route('seller.orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertNotNull($order->fresh()->completed_at);

        $this->get(route('seller.analytics'))
            ->assertOk()
            ->assertSee('Sales analytics')
            ->assertSee('UGX 25,000')
            ->assertSee('Free-range chicken');
    }

    public function test_marketplace_can_search_and_filter_listings(): void
    {
        $farmer = User::factory()->farmer()->create();
        $this->createProduct($farmer, ['name' => 'Free-range chicken']);
        $this->createProduct($farmer, ['name' => 'Layers mash', 'category' => 'feed', 'unit' => 'bag']);

        $this->actingAs(User::factory()->consumer()->create())
            ->get(route('marketplace.index', ['search' => 'chicken']))
            ->assertOk()
            ->assertSee('Free-range chicken')
            ->assertDontSee('Layers mash');

        $this->get(route('marketplace.index', ['category' => 'feed']))
            ->assertOk()
            ->assertSee('Layers mash')
            ->assertDontSee('Free-range chicken');
    }

    private function createProduct(User $seller, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'seller_id' => $seller->id,
            'category' => 'poultry',
            'name' => 'Free-range chicken',
            'price' => 25000,
            'quantity' => 10,
            'unit' => 'bird',
            'status' => 'active',
        ], $attributes));
    }
}
