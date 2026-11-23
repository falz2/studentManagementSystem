<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_poultryhub_roles_and_sample_listings_repeatably(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, User::role('admin')->count());
        $this->assertSame(1, User::role('farmer')->count());
        $this->assertSame(1, User::role('supplier')->count());
        $this->assertSame(1, User::role('consumer')->count());
        $this->assertSame(3, Product::count());

        foreach ([
            'admin@poultryhub.ug' => 'admin',
            'amina@poultryhub.ug' => 'farmer',
            'feeds@poultryhub.ug' => 'supplier',
            'shopper@poultryhub.ug' => 'consumer',
        ] as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($role));
            $this->assertTrue(Hash::check('password', $user->password));
        }

        $this->assertDatabaseHas('products', [
            'name' => 'Free-range chicken',
            'category' => 'poultry',
            'price' => 25000,
        ]);
        $this->assertDatabaseHas('products', [
            'name' => 'Layers mash (50kg)',
            'category' => 'feed',
            'price' => 85000,
        ]);
    }
}
