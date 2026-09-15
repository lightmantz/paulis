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
        // ── Super Admin ────────────────────────────────────────
        SuperAdmin::updateOrCreate(
            ['email' => 'admin@paulispos.co.tz'],
            [
                'name'     => 'System Administrator',
                'password' => Hash::make('Admin@2026'),
            ]
        );

        // ── Business ───────────────────────────────────────────
        $business = Business::updateOrCreate(
            ['slug' => 'paulis'],
            [
                'name'          => "Pauli's Computer Shop",
                'owner_name'    => 'Paulina',
                'email'         => 'paulina@paulis.co.tz',
                'phone'         => '+255 754 000 101',
                'city'          => 'Mwanza',
                'plan'          => 'Professional',
                'status'        => 'Active',
                'monthly_fee'   => 185000,
                'renewal_date'  => now()->addDays(30)->toDateString(),
                'trial_ends_at' => now()->addDays(14),
            ]
        );

        // ── Business Owner ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'paulina@paulis.co.tz'],
            [
                'business_id' => $business->id,
                'name'        => 'Paulina',
                'username'    => 'owner',
                'phone'       => '+255 754 000 101',
                'role'        => 'owner',
                'status'      => 'Active',
                'password'    => Hash::make('1234'),
            ]
        );

        // ── Sales Person ───────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'sales@paulis.co.tz'],
            [
                'business_id' => $business->id,
                'name'        => 'Sarah A.',
                'username'    => 'salesperson',
                'phone'       => '+255 754 000 002',
                'role'        => 'sales_person',
                'status'      => 'Active',
                'password'    => Hash::make('1234'),
            ]
        );

        // ── Repair Person ──────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'repairs@paulis.co.tz'],
            [
                'business_id' => $business->id,
                'name'        => 'Michael J.',
                'username'    => 'repairperson',
                'phone'       => '+255 754 000 003',
                'role'        => 'repair_person',
                'status'      => 'Active',
                'password'    => Hash::make('1234'),
            ]
        );

        $this->command->info('✅ Demo data seeded successfully.');
    }
}
