<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();

            // Unique machine key used by ReminderService to look up templates
            // e.g. 'payment_confirmation', 'overdue_7_days', 'pre_due_3_days'
            $table->string('trigger_key')->unique();

            // Human-readable name shown in the template editor UI
            $table->string('name');

            // Template category for grouping in the UI
            // e.g. 'payment', 'reminder', 'notification', 'overdue'
            $table->string('category', 50)->default('general');

            // The SMS body with {variable} placeholders
            // Max 918 chars (6 SMS pages of 153 chars each)
            $table->string('body', 918);

            // Denormalised length and page count — updated by model hook on save
            $table->unsignedSmallInteger('char_count')->default(0);
            $table->unsignedTinyInteger('sms_pages')->default(1);

            // Maximum body length enforced by the template editor
            $table->unsignedSmallInteger('max_length')->default(459); // 3 pages

            // Whether this template is sent when triggered
            // Inactive templates are silently skipped by ReminderService
            $table->boolean('is_active')->default(true);

            // Audit: who last edited the body
            $table->foreignId('last_edited_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Index for fast lookup by category in the template editor list
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};


// ═══════════════════════════════════════════════════════════════════════════════
// Migration: create_loan_escalations_table
// File: database/migrations/2026_01_01_000022_create_loan_escalations_table.php
//
// Records escalation events for overdue loans that have been referred
// to a collections team, legal, or an external debt collector.
//
// The OverdueController::escalate() method writes here.
// The SendEscalationNotice job reads from here.
// ═══════════════════════════════════════════════════════════════════════════════
