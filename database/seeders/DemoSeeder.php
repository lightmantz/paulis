<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin (system owner)
        SuperAdmin::create([
            'name' => 'System Administrator',
            'email' => 'admin@paulispos.co.tz',
            'password' => Hash::make('Admin@2026'),
        ]);

        // Business account
        $business = Business::create([
            'slug' => 'paulis',
            'name' => "Pauli's Computer Shop",
            'owner_name' => 'Paulina',
            'email' => 'paulina@paulis.co.tz',
            'phone' => '+255 754 000 101',
            'city' => 'Mwanza',
            'plan' => 'Professional',
            'status' => 'Active',
            'monthly_fee' => 185000,
            'renewal_date' => '2026-09-01',
        ]);

        // Business Owner
        User::create([
            'business_id' => $business->id,
            'name' => 'Paulina',
            'email' => 'paulina@paulis.co.tz',
            'username' => 'owner',
            'password' => Hash::make('1234'),
            'role' => 'owner',
        ]);

        // Sales Person
        User::create([
            'business_id' => $business->id,
            'name' => 'Sarah A.',
            'email' => 'sales@paulis.co.tz',
            'username' => 'salesperson',
            'password' => Hash::make('1234'),
            'role' => 'sales_person',
        ]);

        // Repair Person
        User::create([
            'business_id' => $business->id,
            'name' => 'Michael J.',
            'email' => 'repairs@paulis.co.tz',
            'username' => 'repairperson',
            'password' => Hash::make('1234'),
            'role' => 'repair_person',
        ]);

        $this->command->info('✅ Demo data seeded successfully.');
    }
}