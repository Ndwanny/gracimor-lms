<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Running balance snapshot for each loan — updated on every payment event.
     * Kept as a separate table (rather than on the loans table) so that
     * historical balance snapshots can be audited and recalculated without
     * touching the immutable loans record.
     *
     * One row per loan (upserted on each payment).
     */
    public function up(): void
    {
        Schema::create('loan_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->unique()->constrained('loans')->cascadeOnDelete();

            // Principal tracking
            $table->decimal('principal_disbursed', 15, 2)->default(0);
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('principal_outstanding', 15, 2)->default(0);

            // Interest tracking
            $table->decimal('interest_charged', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->decimal('interest_outstanding', 15, 2)->default(0);

            // Penalty tracking
            $table->decimal('penalty_charged', 15, 2)->default(0);
            $table->decimal('penalty_paid', 15, 2)->default(0);
            $table->decimal('penalty_outstanding', 15, 2)->default(0);

            // Total outstanding (principal + interest + penalty)
            $table->decimal('total_outstanding', 15, 2)->default(0);

            // Repayment progress
            $table->unsignedSmallInteger('instalments_total')->default(0);
            $table->unsignedSmallInteger('instalments_paid')->default(0);
            $table->unsignedSmallInteger('instalments_overdue')->default(0);

            // Last updated when a payment was processed
            $table->timestamp('last_payment_at')->nullable();
            $table->decimal('last_payment_amount', 15, 2)->nullable();

            $table->timestamps();

            $table->index('loan_id');
            $table->index('principal_outstanding');
            $table->index('penalty_outstanding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_balances');
    }
};
