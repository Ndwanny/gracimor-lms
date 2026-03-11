<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_escalations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete();

            // Type of escalation
            // 'collections_team' | 'legal' | 'external_collector' | 'write_off_review'
            $table->string('escalation_type', 50);

            // Who the loan was assigned to for follow-up
            // 'collections_manager' | 'legal_team' | 'bailiff' | 'external_agency'
            $table->string('assigned_to', 100);

            // Free-text notes from the manager/CEO who escalated
            $table->text('notes')->nullable();

            // Status lifecycle: open → in_progress → resolved | withdrawn
            $table->string('status', 30)->default('open');

            // Days overdue at the time of escalation (snapshot)
            $table->unsignedSmallInteger('days_overdue_at_escalation')->default(0);

            // Outstanding balance at the time of escalation (snapshot)
            $table->decimal('outstanding_at_escalation', 15, 2)->default(0);

            // Who escalated (the manager/CEO who hit the button)
            $table->foreignId('escalated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // When this escalation was resolved or withdrawn
            $table->timestamp('resolved_at')->nullable();

            // Who resolved it
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            // Indexes for common query patterns
            $table->index('loan_id');
            $table->index('status');
            $table->index(['loan_id', 'status']);
            $table->index('escalation_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_escalations');
    }
};
