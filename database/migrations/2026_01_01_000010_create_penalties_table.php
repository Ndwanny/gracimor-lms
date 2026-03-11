<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Audit log of every penalty event applied to a loan schedule row.
     * The scheduled job ApplyPenaltiesJob writes here daily for every
     * overdue instalment that passes the product's grace_period_days.
     *
     * Multiple penalty rows can exist for the same schedule row if the
     * borrower remains overdue across multiple penalty cycles.
     */
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans');
            $table->foreignId('loan_schedule_id')->constrained('loan_schedule');
            $table->foreignId('borrower_id')->constrained('borrowers');

            $table->decimal('penalty_amount', 15, 2)
                  ->comment('Calculated as: instalment_total_due * penalty_rate / 100');
            $table->decimal('penalty_rate_used', 5, 2)
                  ->comment('Snapshot of the rate at the time of application');
            $table->date('applied_date');
            $table->unsignedSmallInteger('days_overdue_at_application')
                  ->comment('Number of days past the due date when this penalty was applied');

            // Has this specific penalty been cleared by a payment?
            $table->enum('status', ['outstanding', 'paid', 'waived'])->default('outstanding');
            $table->foreignId('cleared_by_payment_id')->nullable()->constrained('payments');
            $table->timestamp('cleared_at')->nullable();

            // Manual waiver
            $table->foreignId('waived_by')->nullable()->constrained('users');
            $table->text('waiver_reason')->nullable();
            $table->timestamp('waived_at')->nullable();

            // System vs manual
            $table->boolean('is_system_generated')->default(true)
                  ->comment('true = applied by cron job; false = manually applied by officer');
            $table->foreignId('applied_by')->nullable()->constrained('users')
                  ->comment('Null for system-generated penalties');

            $table->timestamps();

            $table->index('loan_id');
            $table->index('loan_schedule_id');
            $table->index('borrower_id');
            $table->index('applied_date');
            $table->index('status');
            $table->index(['loan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
