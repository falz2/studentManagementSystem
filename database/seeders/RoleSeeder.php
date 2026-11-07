<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'lecturer', 'student'] as $name) {
            Role::findOrCreate($name, 'web');
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@northstar.edu'],
            [
                'name' => 'Northstar Administrator',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('admin');
    }
}
