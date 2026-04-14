<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password123'),
            'status' => 'active',
            'must_change_password' => false,
        ];

        User::create($base + [
            'full_name' => 'Admin Account',
            'email' => 'admin@redrms.co.ke',
            'role' => 'admin',
            'must_change_password' => true, // force admin reset if needed
        ]);

        User::create($base + [
            'full_name' => 'Landlord Account 1',
            'email' => 'landlord@redrms.co.ke',
            'role' => 'landlord',
        ]);

        User::create($base + [
            'full_name' => 'Manager Account',
            'email' => 'manager@redrms.co.ke',
            'role' => 'manager',
        ]);

        User::create($base + [
            'full_name' => 'Tenant Account',
            'email' => 'tenant@redrms.co.ke',
            'role' => 'tenant',
        ]);
    }
}