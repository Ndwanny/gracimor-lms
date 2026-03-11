<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Document store for all files attached to borrowers, loans, and collateral.
     * The morphable (documentable) polymorphic relationship allows a document
     * to belong to a Borrower, Loan, or CollateralAsset without separate tables.
     *
     * Physical files are stored in cloud/disk storage; only the path is kept here.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Polymorphic owner: Borrower | Loan | CollateralAsset
            $table->morphs('documentable');     // adds documentable_id + documentable_type

            // Document classification
            $table->enum('document_type', [
                'national_id',
                'payslip',
                'bank_statement',
                'vehicle_logbook',
                'vehicle_photos',
                'land_title_deed',
                'valuation_report',
                'loan_agreement',
                'guarantor_id',
                'proof_of_residence',
                'other',
            ])->default('other');

            $table->string('display_name', 200)
                  ->comment('Human-readable label shown in the UI');
            $table->string('file_path', 500)
                  ->comment('Storage path / S3 key');
            $table->string('file_name', 255)
                  ->comment('Original filename as uploaded');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('disk', 20)->default('local')
                  ->comment('Laravel storage disk: local | s3 | etc.');

            // Optional: which month/period the doc covers (e.g. payslip month)
            $table->string('period_label', 50)->nullable()
                  ->comment('e.g. "December 2025" for a payslip');

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();

            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('document_type');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
