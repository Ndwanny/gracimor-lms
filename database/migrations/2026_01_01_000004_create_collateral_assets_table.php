<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores all collateral assets (vehicles and land) belonging to borrowers.
     * An asset can be linked to at most one active loan at a time (enforced in app logic).
     */
    public function up(): void
    {
        Schema::create('collateral_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained('borrowers')->cascadeOnDelete();
            $table->enum('asset_type', ['vehicle', 'land']);

            // ── VEHICLE FIELDS ────────────────────────────────────────
            $table->string('vehicle_registration', 30)->nullable()->comment('e.g. ZMB 4521C');
            $table->string('vehicle_make', 80)->nullable();         // e.g. Toyota
            $table->string('vehicle_model', 80)->nullable();        // e.g. Hilux
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('vehicle_color', 40)->nullable();
            $table->string('engine_number', 60)->nullable();
            $table->string('chassis_vin', 60)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('insurance_company', 100)->nullable();

            // ── LAND FIELDS ────────────────────────────────────────────
            $table->string('plot_number', 60)->nullable();
            $table->string('title_deed_number', 80)->nullable();
            $table->text('land_address')->nullable();
            $table->decimal('land_size_sqm', 12, 2)->nullable();
            $table->enum('land_ownership_type', ['freehold', 'leasehold', 'customary'])->nullable();
            $table->enum('land_use', ['residential', 'commercial', 'agricultural', 'mixed'])->nullable();
            $table->decimal('gps_latitude', 10, 7)->nullable();
            $table->decimal('gps_longitude', 10, 7)->nullable();

            // ── VALUATION ──────────────────────────────────────────────
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('valuer_name', 150)->nullable();
            $table->string('valuation_firm', 150)->nullable();

            // ── STATUS ─────────────────────────────────────────────────
            // pledged = currently used as collateral on an active loan
            // available = owned by borrower, not pledged
            // released = was pledged, now released after loan closed
            $table->enum('status', ['available', 'pledged', 'released'])->default('available');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('borrower_id');
            $table->index('asset_type');
            $table->index('status');
            $table->index('vehicle_registration');
            $table->index('plot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collateral_assets');
    }
};
