<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_farmer_can_create_update_and_remove_a_product_listing(): void
    {
        $farmer = User::factory()->farmer()->create();

        $this->actingAs($farmer)->post(route('seller.products.store'), [
            'category' => 'poultry',
            'name' => 'Free-range chicken',
            'description' => 'Farm raised',
            'price' => 25000,
            'quantity' => 12,
            'unit' => 'bird',
            'status' => 'active',
        ])->assertRedirect(route('seller.products.index'));

        $product = Product::firstOrFail();
        $this->assertSame($farmer->id, $product->seller_id);
        $this->assertDatabaseHas('products', ['name' => 'Free-range chicken', 'price' => 25000]);

        $this->put(route('seller.products.update', $product), [
            'category' => 'poultry',
            'name' => 'Free-range eggs',
            'description' => '',
            'price' => 12000,
            'quantity' => 20,
            'unit' => 'tray',
            'status' => 'active',
        ])->assertRedirect(route('seller.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Free-range eggs', 'quantity' => 20]);
        $this->delete(route('seller.products.destroy', $product))
            ->assertRedirect(route('seller.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_supplier_can_only_list_feed_and_cannot_edit_another_sellers_product(): void
    {
        $supplier = User::factory()->supplier()->create();
        $farmer = User::factory()->farmer()->create();
        $product = Product::create([
            'seller_id' => $farmer->id,
            'category' => 'poultry',
            'name' => 'Chicken',
            'price' => 25000,
            'quantity' => 4,
            'unit' => 'bird',
            'status' => 'active',
        ]);

        $this->actingAs($supplier)->post(route('seller.products.store'), [
            'category' => 'poultry',
            'name' => 'Chicken feed',
            'price' => 20000,
            'quantity' => 5,
            'unit' => 'bag',
            'status' => 'active',
        ])->assertSessionHasErrors('category');

        $this->get(route('seller.products.edit', $product))->assertNotFound();
        $this->assertSame(1, Product::count());
    }
}
