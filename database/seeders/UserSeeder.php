<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [

            // ── Superadmin (ID will be 1) ──────────────────────────────────
            [
                'name'      => 'System Administrator',
                'email'     => 'superadmin@gracimor.co.zm',
                'password'  => Hash::make('SuperAdmin1'),
                'role'      => 'superadmin',
                'phone'     => '+260977000000',
                'is_active' => true,
            ],

            // ── CEO ────────────────────────────────────────────────────────
            [
                'name'      => 'Mwansa Chanda',
                'email'     => 'ceo@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'ceo',
                'phone'     => '+260977000001',
                'is_active' => true,
            ],

            // ── Managers ───────────────────────────────────────────────────
            [
                'name'      => 'Bupe Banda',
                'email'     => 'manager.banda@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'manager',
                'phone'     => '+260977000002',
                'is_active' => true,
            ],
            [
                'name'      => 'Mutale Phiri',
                'email'     => 'manager.phiri@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'manager',
                'phone'     => '+260977000003',
                'is_active' => true,
            ],

            // ── Loan Officers ──────────────────────────────────────────────
            [
                'name'      => 'Kaputo Tembo',
                'email'     => 'officer.tembo@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'officer',
                'phone'     => '+260977000010',
                'is_active' => true,
            ],
            [
                'name'      => 'Chileshe Daka',
                'email'     => 'officer.daka@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'officer',
                'phone'     => '+260977000011',
                'is_active' => true,
            ],
            [
                'name'      => 'Mwila Sichone',
                'email'     => 'officer.sichone@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'officer',
                'phone'     => '+260977000012',
                'is_active' => true,
            ],
            [
                'name'      => 'Lombe Moonga',
                'email'     => 'officer.moonga@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'officer',
                'phone'     => '+260977000013',
                'is_active' => true,
            ],
            [
                'name'      => 'Kunda Mulenga',
                'email'     => 'officer.mulenga@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'officer',
                'phone'     => '+260977000014',
                'is_active' => true,
            ],

            // ── Accountants ────────────────────────────────────────────────
            [
                'name'      => 'Natasha Zulu',
                'email'     => 'accounts.zulu@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'accountant',
                'phone'     => '+260977000020',
                'is_active' => true,
            ],
            [
                'name'      => 'Bridget Kaluba',
                'email'     => 'accounts.kaluba@gracimor.co.zm',
                'password'  => Hash::make('Password1'),
                'role'      => 'accountant',
                'phone'     => '+260977000021',
                'is_active' => true,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['email_verified_at' => now()])
            );
        }

        $this->command->info('✓ ' . count($users) . ' staff accounts seeded.');
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// LoanProductSeeder
// File: database/seeders/LoanProductSeeder.php
//
// Seeds 4 realistic Gracimor loan products.
// Products are stable — referenced by name in tests.
// ═══════════════════════════════════════════════════════════════════════════════
