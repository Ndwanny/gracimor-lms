<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();

            // The model that owns this token (morphable — defaults to App\Models\User)
            $table->morphs('tokenable');

            // Human-readable label set at token creation ("api-token", etc.)
            $table->string('name');

            // SHA-256 hash of the plain token string
            $table->string('token', 64)->unique();

            // JSON array of abilities granted to this token
            // e.g. ["*"] = all abilities, ["read:loans"] = scoped
            $table->text('abilities')->nullable();

            // Timestamps
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // morphs() already adds the composite index for tokenable_type + tokenable_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};


// ═══════════════════════════════════════════════════════════════════════════════
// Migration: create_sms_templates_table
// File: database/migrations/2026_01_01_000021_create_sms_templates_table.php
//
// Stores configurable SMS template bodies.
// Every template has a unique trigger_key that the ReminderService uses
// to load the correct body at send time.
//
// The body column uses {variable} placeholders.
// The char_count and sms_pages columns are denormalised from the body
// and updated automatically by the SmsTemplate model's saving() hook.
// ═══════════════════════════════════════════════════════════════════════════════
