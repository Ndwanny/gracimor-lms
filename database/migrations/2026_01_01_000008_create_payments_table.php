<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Records every payment transaction against a loan.
     * A single payment can be allocated across multiple schedule rows
     * (handled via payment_allocations). The payments table stores the
     * total receipt; allocations store the per-instalment breakdown.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Auto-generated receipt number, e.g. RCP-00891
            $table->string('receipt_number', 20)->unique();

            $table->foreignId('loan_id')->constrained('loans');
            $table->foreignId('borrower_id')->constrained('borrowers');

            // ── AMOUNTS ───────────────────────────────────────────────
            $table->decimal('amount_received', 15, 2)
                  ->comment('Total cash/transfer amount received from borrower');
            $table->decimal('towards_principal', 15, 2)->default(0);
            $table->decimal('towards_interest', 15, 2)->default(0);
            $table->decimal('towards_penalty', 15, 2)->default(0);
            $table->decimal('overpayment', 15, 2)->default(0)
                  ->comment('Any excess amount received beyond total due');

            // ── BALANCE SNAPSHOTS ──────────────────────────────────────
            $table->decimal('balance_before', 15, 2)
                  ->comment('loan_balances.total_outstanding before this payment');
            $table->decimal('balance_after', 15, 2)
                  ->comment('loan_balances.total_outstanding after this payment');

            // ── TYPE ──────────────────────────────────────────────────
            $table->enum('payment_type', [
                'instalment',       // regular scheduled instalment payment
                'partial',          // less than full instalment amount
                'early_settlement', // pays off remaining balance in full
                'penalty',          // penalty-only payment
                'overpayment',      // more than total due
            ])->default('instalment');

            // ── METHOD ────────────────────────────────────────────────
            $table->enum('payment_method', ['cash', 'mobile_money', 'bank_transfer', 'cheque'])
                  ->default('cash');
            $table->string('payment_reference', 150)->nullable()
                  ->comment('Mobile money TXN ID / bank narration / cheque number');
            $table->string('payment_provider', 80)->nullable()
                  ->comment('e.g. Airtel Money, MTN MoMo, Zanaco');

            // ── DATES ─────────────────────────────────────────────────
            $table->date('payment_date')
                  ->comment('Effective date of the payment (may differ from created_at for backdated entries)');

            // ── NOTES & STATUS ────────────────────────────────────────
            $table->text('notes')->nullable();
            $table->boolean('is_reversed')->default(false);
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->text('reversal_reason')->nullable();

            // Recorded by
            $table->foreignId('recorded_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index('receipt_number');
            $table->index('loan_id');
            $table->index('borrower_id');
            $table->index('payment_date');
            $table->index('payment_type');
            $table->index('payment_method');
            $table->index(['loan_id', 'payment_date']);
            $table->index('is_reversed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
