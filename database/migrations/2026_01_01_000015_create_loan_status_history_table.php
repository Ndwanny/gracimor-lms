<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Full chronological history of every status change on a loan.
     * Provides the data for the Loan Lifecycle Timeline in the UI.
     *
     * Written by LoanStatusService whenever loans.status changes.
     */
    public function up(): void
    {
        Schema::create('loan_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();

            $table->string('from_status', 30)->nullable()
                  ->comment('Null for the first entry (loan created/drafted)');
            $table->string('to_status', 30);

            $table->text('notes')->nullable()
                  ->comment('Approval notes, rejection reason, disbursement info, etc.');

            // Actor
            $table->foreignId('changed_by')->nullable()->constrained('users')
                  ->comment('Null for system-triggered transitions');
            $table->boolean('is_system_action')->default(false);

            // Snapshot of key values at the time of transition
            $table->json('metadata')->nullable()
                  ->comment('JSON bag: approved_amount, disbursed_amount, settlement_amount, etc.');

            $table->timestamp('created_at')->useCurrent();

            $table->index('loan_id');
            $table->index('to_status');
            $table->index(['loan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_status_history');
    }
};
