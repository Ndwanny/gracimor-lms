<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Breaks a single payment down into per-instalment allocations.
     * Priority order (enforced in PaymentAllocationService):
     *   1. Penalties (oldest first)
     *   2. Interest
     *   3. Principal
     *
     * A payment that covers multiple overdue instalments will create
     * multiple allocation rows — one per schedule entry touched.
     */
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('loan_schedule_id')->constrained('loan_schedule');
            $table->foreignId('loan_id')->constrained('loans');

            // Amounts allocated to this specific schedule row
            $table->decimal('allocated_principal', 15, 2)->default(0);
            $table->decimal('allocated_interest', 15, 2)->default(0);
            $table->decimal('allocated_penalty', 15, 2)->default(0);
            $table->decimal('allocated_total', 15, 2)
                  ->comment('Sum of the three allocated columns');

            // Was this allocation enough to fully close the instalment?
            $table->boolean('instalment_fully_paid')->default(false);

            $table->timestamps();

            $table->index('payment_id');
            $table->index('loan_schedule_id');
            $table->index('loan_id');
            $table->index(['payment_id', 'loan_schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
