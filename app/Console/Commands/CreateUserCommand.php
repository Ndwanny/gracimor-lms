<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    protected $signature = 'app:create-user
        {--name=      : Full name of the staff member}
        {--email=     : Email address}
        {--role=      : Role: superadmin|ceo|manager|officer|accountant}
        {--phone=     : Phone in E.164 format (+260977000001)}
        {--password=  : Password (will be prompted if omitted)}
        {--no-verify  : Do not mark email as verified}';

    protected $description = 'Create a new staff user account interactively.';

    private const VALID_ROLES = ['superadmin', 'ceo', 'manager', 'officer', 'accountant'];

    public function handle(): int
    {
        $this->info('Creating new Gracimor staff account.');
        $this->newLine();

        // ── Collect name ──────────────────────────────────────────────────────
        $name = $this->option('name') ?: $this->ask('Full name');
        while (strlen(trim($name)) < 3) {
            $this->error('Name must be at least 3 characters.');
            $name = $this->ask('Full name');
        }

        // ── Collect email ─────────────────────────────────────────────────────
        $email = $this->option('email') ?: $this->ask('Email address');
        while (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');
            $email = $this->ask('Email address');
        }
        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");
            return self::FAILURE;
        }

        // ── Collect role ──────────────────────────────────────────────────────
        $role = $this->option('role');
        if (!$role || !in_array($role, self::VALID_ROLES)) {
            $role = $this->choice(
                'Role',
                self::VALID_ROLES,
                'officer'
            );
        }

        // ── Collect phone ─────────────────────────────────────────────────────
        $phone = $this->option('phone') ?: $this->ask('Phone (E.164 format, e.g. +260977000001)');
        while (!preg_match('/^\+260[79]\d{8}$/', $phone)) {
            $this->error('Phone must be a valid Zambian number in E.164 format (e.g. +260977000001).');
            $phone = $this->ask('Phone');
        }

        // ── Collect password ──────────────────────────────────────────────────
        $password = $this->option('password');

        if (!$password) {
            $password = $this->secret('Password (min 8 chars, at least one number)');
            $confirm  = $this->secret('Confirm password');

            if ($password !== $confirm) {
                $this->error('Passwords do not match.');
                return self::FAILURE;
            }
        }

        $pwdValidation = Validator::make(
            ['password' => $password],
            ['password' => 'required|min:8|regex:/[0-9]/']
        );

        if ($pwdValidation->fails()) {
            $this->error('Password must be at least 8 characters and contain at least one number.');
            return self::FAILURE;
        }

        // ── Confirm ───────────────────────────────────────────────────────────
        $this->newLine();
        $this->table(['Field', 'Value'], [
            ['Name',  $name],
            ['Email', $email],
            ['Role',  $role],
            ['Phone', $phone],
        ]);

        if (!$this->confirm('Create this account?', true)) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        // ── Create user ───────────────────────────────────────────────────────
        try {
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => $role,
                'phone'             => $phone,
                'is_active'         => true,
                'email_verified_at' => $this->option('no-verify') ? null : now(),
            ]);

            $this->newLine();
            $this->info("✓ User created successfully (ID: {$user->id})");
            $this->line("  Email: {$email}");
            $this->line("  Role:  {$role}");

            if ($this->option('no-verify')) {
                $this->warn('  Email is NOT verified — user must verify before logging in.');
            }

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Failed to create user: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}


// ═══════════════════════════════════════════════════════════════════════════════
// SmsPreviewCommand  (updated)
// File: app/Console/Commands/SmsPreviewCommand.php
//
// Signature:  sms:preview
//
// Renders any SMS template against demo or live loan data.
// Now includes --context flag for JSON variable override.
//
// Usage:
//   php artisan sms:preview --list
//   php artisan sms:preview overdue_7_days
//   php artisan sms:preview overdue_7_days 42
//   php artisan sms:preview overdue_7_days 42 --send
//   php artisan sms:preview payment_confirmation --context='{"first_name":"Mwansa","amount_paid":"5000.00"}'
// ═══════════════════════════════════════════════════════════════════════════════
