<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoultryHubRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_products_and_marketplace_orders_are_related(): void
    {
        $farmer = User::factory()->farmer()->create();
        $consumer = User::factory()->consumer()->create();
        $product = Product::create([
            'seller_id' => $farmer->id,
            'category' => 'poultry',
            'name' => 'Free-range chicken',
            'price' => 25000,
            'quantity' => 10,
            'unit' => 'bird',
            'status' => 'active',
        ]);
        $order = Order::create([
            'buyer_id' => $consumer->id,
            'seller_id' => $farmer->id,
            'order_number' => 'PH-TEST-ORDER',
            'total' => 50000,
            'status' => 'pending',
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'category' => $product->category,
            'unit' => $product->unit,
            'quantity' => 2,
            'unit_price' => $product->price,
            'total' => 50000,
        ]);

        $this->assertSame($farmer->id, $product->seller->id);
        $this->assertTrue($farmer->products->contains($product));
        $this->assertSame($consumer->id, $order->buyer->id);
        $this->assertSame($farmer->id, $order->seller->id);
        $this->assertTrue($consumer->purchases->contains($order));
        $this->assertTrue($farmer->sales->contains($order));
        $this->assertSame($product->id, $item->product->id);
        $this->assertTrue($order->items->contains($item));
    }
}
