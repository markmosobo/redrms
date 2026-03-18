<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'full_name'              => 'Landlord Account',
            'email'             => 'landlord@redrms.co.ke',
            'email_verified_at' => Carbon::now(),
            'role'              => 'landlord',
            'password'          => Hash::make('password123'),
        ]);

        User::create([
            'full_name'              => 'Manager Account',
            'email'             => 'manager@redrms.co.ke',
            'email_verified_at' => Carbon::now(),
            'role'              => 'manager',
            'password'          => Hash::make('password123'),
        ]);        

        User::create([
            'full_name'              => 'Tenant Account',
            'email'             => 'tenant@redrms.co.ke',
            'email_verified_at' => Carbon::now(),
            'role'              => 'tenant',
            'password'          => Hash::make('password123'),
        ]);
        
    }
}
