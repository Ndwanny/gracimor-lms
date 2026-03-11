<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Central loans table. One row per loan application/agreement.
     * Financial totals are stored at origination and recalculated into
     * loan_schedule rows. Running balances live in loan_balances.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            // Human-readable loan number, e.g. LN-20260032
            $table->string('loan_number', 20)->unique();

            // Relationships
            $table->foreignId('borrower_id')->constrained('borrowers');
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->foreignId('collateral_asset_id')->nullable()->constrained('collateral_assets');

            // ── LOAN TERMS (as approved/disbursed) ───────────────────
            $table->decimal('principal_amount', 15, 2)
                  ->comment('Amount approved and to be (or already) disbursed');
            $table->decimal('interest_rate', 5, 2)
                  ->comment('Annual interest rate % — may differ from product default');
            $table->enum('interest_method', ['reducing_balance', 'flat_rate'])
                  ->default('flat_rate');
            $table->unsignedSmallInteger('term_months');
            $table->date('first_repayment_date')->nullable();

            // ── CALCULATED TOTALS (set on approval/disbursement) ─────
            $table->decimal('total_interest', 15, 2)->default(0);
            $table->decimal('total_repayable', 15, 2)->default(0);
            $table->decimal('monthly_instalment', 15, 2)->default(0);
            $table->decimal('processing_fee', 15, 2)->default(0);

            // LTV snapshot at origination
            $table->decimal('ltv_at_origination', 5, 2)->nullable()
                  ->comment('Loan-to-Value % at time of application');

            // ── DISBURSEMENT ──────────────────────────────────────────
            $table->enum('disbursement_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque'])
                  ->nullable();
            $table->string('disbursement_reference', 100)->nullable();
            $table->date('disbursed_at')->nullable();
            $table->date('maturity_date')->nullable();

            // ── STATUS LIFECYCLE ──────────────────────────────────────
            // draft → pending → approved → disbursed (active) → closed / defaulted
            // rejected is a terminal state from pending
            $table->enum('status', [
                'draft',
                'pending',
                'pending_approval',
                'approved',
                'active',
                'overdue',
                'closed',
                'defaulted',
                'written_off',
                'rejected',
            ])->default('draft');

            // Early settlement
            $table->boolean('is_early_settled')->default(false);
            $table->date('early_settled_at')->nullable();
            $table->decimal('early_settlement_amount', 15, 2)->nullable();
            $table->decimal('early_settlement_discount', 15, 2)->nullable()
                  ->comment('Interest waived on early settlement');

            // Rejection
            $table->text('rejection_reason')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();

            // Loan purpose (free text from application form)
            $table->text('loan_purpose')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('disburse_notes')->nullable();

            // Workflow actors
            $table->foreignId('applied_by')->constrained('users')
                  ->comment('Officer who captured the application');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Frequently queried combinations
            $table->index('loan_number');
            $table->index('borrower_id');
            $table->index('status');
            $table->index(['borrower_id', 'status']);
            $table->index('disbursed_at');
            $table->index('maturity_date');
            $table->index('applied_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
