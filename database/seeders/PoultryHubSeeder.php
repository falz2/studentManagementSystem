<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class PoultryHubSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Amina Namusoke', 'email' => 'amina@poultryhub.ug', 'role' => 'farmer'],
            ['name' => 'Kampala Feeds Co.', 'email' => 'feeds@poultryhub.ug', 'role' => 'supplier'],
            ['name' => 'Demo Shopper', 'email' => 'shopper@poultryhub.ug', 'role' => 'consumer'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => 'password', 'email_verified_at' => now()]
            );
            $user->syncRoles($account['role']);
        }

        $farmer = User::where('email', 'amina@poultryhub.ug')->firstOrFail();
        $supplier = User::where('email', 'feeds@poultryhub.ug')->firstOrFail();
        $products = [
            [
                'seller_id' => $farmer->id,
                'category' => 'poultry',
                'name' => 'Free-range chicken',
                'description' => 'Healthy, locally raised birds from a smallholder farm.',
                'price' => 25000,
                'quantity' => 50,
                'unit' => 'bird',
                'status' => 'active',
            ],
            [
                'seller_id' => $farmer->id,
                'category' => 'poultry',
                'name' => 'Fresh eggs',
                'description' => 'Farm-fresh eggs, collected daily.',
                'price' => 12000,
                'quantity' => 100,
                'unit' => 'tray',
                'status' => 'active',
            ],
            [
                'seller_id' => $supplier->id,
                'category' => 'feed',
                'name' => 'Layers mash (50kg)',
                'description' => 'Balanced feed for laying hens.',
                'price' => 85000,
                'quantity' => 20,
                'unit' => 'bag',
                'status' => 'active',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['seller_id' => $product['seller_id'], 'name' => $product['name']],
                $product
            );
        }
    }
}
