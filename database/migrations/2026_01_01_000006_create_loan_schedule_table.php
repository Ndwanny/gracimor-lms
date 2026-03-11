<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Amortisation schedule — one row per instalment per loan.
     * Generated automatically on disbursement by LoanScheduleService.
     * Each row tracks its own paid/unpaid status and any penalties applied.
     */
    public function up(): void
    {
        Schema::create('loan_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();

            // Instalment sequence number (1-based)
            $table->unsignedSmallInteger('instalment_number');

            // Scheduled due date
            $table->date('due_date');

            // Breakdown (reducing balance or flat rate)
            $table->decimal('principal_portion', 15, 2);
            $table->decimal('interest_portion', 15, 2);
            $table->decimal('total_due', 15, 2)
                  ->comment('principal_portion + interest_portion');

            // Opening / closing balance for this row
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('closing_balance', 15, 2);

            // Penalty applied to this instalment (set by PenaltyService job)
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->date('penalty_applied_at')->nullable();
            $table->unsignedSmallInteger('days_overdue')->default(0);

            // Payment tracking
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('penalty_paid', 15, 2)->default(0);
            $table->date('paid_at')->nullable();          // date fully satisfied

            // Status of this specific instalment row
            $table->enum('status', [
                'pending',   // future, not yet due
                'due',       // due date reached, unpaid
                'partial',   // partially paid, still open
                'paid',      // fully paid (principal + interest + penalty)
                'overdue',   // past due date, unpaid or partially paid
                'waived',    // manually waived by officer
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['loan_id', 'instalment_number']);
            $table->index('loan_id');
            $table->index('due_date');
            $table->index('status');
            $table->index(['due_date', 'status']);   // for daily cron queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_schedule');
    }
};
