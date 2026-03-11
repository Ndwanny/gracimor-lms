<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $env = app()->environment();

        match (true) {

            // ── Production / Staging: minimum safe seed ─────────────────────
            in_array($env, ['production', 'staging']) => $this->call([
                UserSeeder::class,
                LoanProductSeeder::class,
                SmsTemplateSeeder::class,
            ]),

            // ── Testing: deterministic fixture dataset ──────────────────────
            $env === 'testing' => $this->call([
                TestingSeeder::class,
                SmsTemplateSeeder::class,
            ]),

            // ── Local / Development: full realistic dataset ─────────────────
            default => $this->call([
                UserSeeder::class,
                LoanProductSeeder::class,
                SmsTemplateSeeder::class,
                DevelopmentSeeder::class,
            ]),

        };
    }
}
