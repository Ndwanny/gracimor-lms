<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Loan guarantors — third parties who co-sign a loan.
     * A guarantor is linked to a specific loan (not just a borrower)
     * so that different loans can have different guarantors.
     */
    public function up(): void
    {
        Schema::create('guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('borrower_id')->constrained('borrowers')
                  ->comment('The borrower this guarantor is backing');

            // Guarantor personal details
            $table->string('full_name', 150);
            $table->string('nrc_number', 30)->nullable();
            $table->string('phone', 20);
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();
            $table->string('relationship', 60)->nullable()
                  ->comment('e.g. Brother, Spouse, Employer');

            // Employment / financial standing
            $table->enum('employment_status', [
                'employed', 'self_employed', 'business_owner', 'unemployed', 'other'
            ])->nullable();
            $table->string('employer_name', 150)->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();

            $table->enum('status', ['active', 'released', 'defaulted'])->default('active');
            $table->text('notes')->nullable();

            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('loan_id');
            $table->index('borrower_id');
            $table->index('nrc_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantors');
    }
};
