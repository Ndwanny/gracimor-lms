<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Core borrower registry — one record per individual client.
     * A borrower can have multiple loans over time.
     */
    public function up(): void
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();

            // Auto-generated borrower number, e.g. BRW-00201
            $table->string('borrower_number', 20)->unique();

            // Personal details
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('nrc_number', 30)->unique()->comment('National Registration Card / national ID — must be unique');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();

            // Contact
            $table->string('phone_primary', 20);
            $table->string('phone_secondary', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('residential_address')->nullable();
            $table->string('city_town', 80)->nullable();

            // Employment & income
            $table->enum('employment_status', [
                'employed', 'self_employed', 'business_owner', 'unemployed', 'other'
            ])->nullable();
            $table->string('employer_name', 150)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->text('work_address')->nullable();

            // KYC / status
            $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('kyc_verified_at')->nullable();
            $table->foreignId('kyc_verified_by')->nullable()->constrained('users');
            $table->text('internal_notes')->nullable();

            // Avatar / photo
            $table->string('photo_path')->nullable();

            // Assigned loan officer
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users');

            // Registered by
            $table->foreignId('registered_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index('borrower_number');
            $table->index('nrc_number');
            $table->index('phone_primary');
            $table->index(['first_name', 'last_name']);
            $table->index('kyc_status');
            $table->index('assigned_officer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowers');
    }
};
