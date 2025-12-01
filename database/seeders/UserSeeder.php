<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\ReferralCode;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@electronicsheet.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create user settings for admin
        UserSetting::updateOrCreate(
            ['user_id' => $admin->id],
            []
        );

        // Create referral codes
        $userRole = Role::where('name', 'user')->first();
        $guestRole = Role::where('name', 'guest')->first();

        ReferralCode::updateOrCreate(
            ['code' => 'USER-2024'],
            [
                'created_by' => $admin->id,
                'assigned_role_id' => $userRole->id,
                'note' => 'Referral code for new users',
                'is_active' => true,
            ]
        );

        ReferralCode::updateOrCreate(
            ['code' => 'GUEST-2024'],
            [
                'created_by' => $admin->id,
                'assigned_role_id' => $guestRole->id,
                'note' => 'Referral code for guests',
                'is_active' => true,
            ]
        );
    }
}
