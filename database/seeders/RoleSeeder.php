<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access + manage users & referral codes',
                'level' => 100,
            ],
            [
                'name' => 'user',
                'display_name' => 'Regular User',
                'description' => 'Create, edit, delete documents, file management',
                'level' => 50,
            ],
            [
                'name' => 'guest',
                'display_name' => 'Guest User',
                'description' => 'View documents only',
                'level' => 10,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
