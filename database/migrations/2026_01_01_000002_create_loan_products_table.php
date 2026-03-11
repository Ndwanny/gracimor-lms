<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Defines the loan product catalogue (e.g. Vehicle-Backed Loan, Land-Backed Loan).
     * All financial rates and fee rules are stored here and inherited by individual loans.
     */
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                    // e.g. "Vehicle-Backed Loan"
            $table->string('code', 30)->unique();           // e.g. "VBL", "LBL"
            $table->text('description')->nullable();
            $table->enum('collateral_type', ['vehicle', 'land', 'both', 'none'])
                  ->default('vehicle');

            // Interest configuration
            $table->decimal('default_interest_rate', 5, 2);          // annual %, e.g. 28.00
            $table->enum('interest_method', ['reducing_balance', 'flat_rate'])
                  ->default('flat_rate');
            $table->decimal('min_interest_rate', 5, 2)->default(10);
            $table->decimal('max_interest_rate', 5, 2)->default(38);

            // Term limits (months)
            $table->unsignedSmallInteger('min_term_months')->default(1);
            $table->unsignedSmallInteger('max_term_months')->default(4);
            $table->unsignedSmallInteger('default_term_months')->default(3);

            // Loan amount limits
            $table->decimal('min_loan_amount', 15, 2)->default(0);
            $table->decimal('max_loan_amount', 15, 2)->nullable();    // null = no upper limit

            // LTV ratio
            $table->decimal('max_ltv_percent', 5, 2)->default(80.00); // max Loan-to-Value %

            // Fees
            $table->decimal('processing_fee_fixed', 15, 2)->default(0);  // flat fee on disbursement
            $table->decimal('processing_fee_percent', 5, 2)->default(0); // % of principal

            // Penalty configuration
            $table->decimal('penalty_rate_percent', 5, 2)->default(5.00); // % of overdue instalment
            $table->unsignedSmallInteger('grace_period_days')->default(0); // days before penalty kicks in

            // Early settlement
            $table->boolean('allow_early_settlement')->default(true);
            $table->enum('early_settlement_method', ['prorated', 'rebate_78', 'none'])
                  ->default('prorated');

            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('collateral_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
