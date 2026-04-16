<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Tenancy;
use Illuminate\Support\Carbon;

class PropertyUnitTenancySeeder extends Seeder
{
    public function run(): void
    {
        // 🔎 Fetch required users
        $landlord = User::where('role', 'landlord')->first();
        $manager  = User::where('role', 'manager')->first();
        $tenant   = User::where('role', 'tenant')->first();

        if (!$landlord || !$tenant) {
            $this->command->warn('Landlord or Tenant not found. Seeder skipped.');
            return;
        }

        // 🏠 Create Property
        $property = Property::create([
            'landlord_id'  => $landlord->id,
            'manager_id'   => $manager?->id,
            'property_name'=> 'Villa Nova',
            'location'     => 'Kapsokwony',
            'description'  => 'Mixed-use residential & commercial property'
        ]);

        // 🚪 Create Units
        $unit1 = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'Shop 1',
            'unit_type'   => 'Commercial',
            'rent_amount'=> 6000,
            'status'      => 'occupied'
        ]);

        $unit2 = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'Shop 2',
            'unit_type'   => 'Commercial',
            'rent_amount'=> 6000,
            'status'      => 'vacant'
        ]);

        $this->command->info('Property, Units & Tenancy seeded successfully.');
    }
}