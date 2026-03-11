<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Key-value store for system-wide configuration that administrators
     * can change without a code deployment.
     *
     * Examples: company name, logo path, SMS provider credentials,
     * reminder schedule, penalty grace period override, etc.
     *
     * Values are stored as strings; cast in the Setting model.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')
                  ->comment('string | integer | float | boolean | json');
            $table->string('group', 60)->default('general')
                  ->comment('Logical grouping: general | sms | email | loans | penalties | reminders');
            $table->string('label', 200)->nullable()
                  ->comment('Human-readable label for the settings UI');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)
                  ->comment('If true, value can be exposed to authenticated frontend clients');
            $table->boolean('is_encrypted')->default(false)
                  ->comment('If true, value is encrypted at rest (use for API keys)');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('group');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
