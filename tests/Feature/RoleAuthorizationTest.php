<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_marketplace_and_orders_require_authentication(): void
    {
        $this->get('/marketplace')->assertRedirect('/login');
        $this->get('/orders')->assertRedirect('/login');
    }

    public function test_sellers_can_manage_listings_and_incoming_orders(): void
    {
        foreach (['farmer', 'supplier'] as $role) {
            $user = User::factory()->{$role}()->create();

            $this->actingAs($user)->get('/seller')->assertOk();
            $this->actingAs($user)->get('/seller/products')->assertOk();
            $this->actingAs($user)->get('/seller/products/create')->assertOk();
            $this->actingAs($user)->get('/seller/orders')->assertOk();
            $this->actingAs($user)->get('/seller/analytics')->assertOk();
        }
    }

    public function test_consumers_can_shop_but_cannot_access_seller_tools(): void
    {
        $consumer = User::factory()->consumer()->create();

        $this->actingAs($consumer)->get('/marketplace')->assertOk();
        $this->actingAs($consumer)->get('/cart')->assertOk();
        $this->actingAs($consumer)->get('/orders')->assertOk();
        $this->actingAs($consumer)->get('/seller')->assertForbidden();
        $this->actingAs($consumer)->get('/seller/products')->assertForbidden();
        $this->actingAs($consumer)->get('/seller/orders')->assertForbidden();
    }

    public function test_admin_is_separate_from_public_seller_roles(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/seller')->assertForbidden();
    }

    public function test_unknown_roles_are_denied_by_default(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('unknown', 'web'));

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/seller')->assertForbidden();
    }
}
