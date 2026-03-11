<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Immutable audit trail for every significant action in the system.
     * Written by the AuditObserver and explicitly in service classes.
     * Rows are NEVER updated or soft-deleted — they are append-only.
     *
     * Captures: who did what, to which record, from where, and what changed.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Actor
            $table->foreignId('user_id')->nullable()->constrained('users')
                  ->comment('Null for system/job actions');
            $table->string('user_role', 40)->nullable()
                  ->comment('Snapshot of role at time of action');

            // Action
            $table->string('action', 100)
                  ->comment('e.g. loan.approved, payment.recorded, borrower.created');
            $table->string('description', 500)->nullable();

            // Affected record (polymorphic)
            $table->string('auditable_type', 100)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            // Payload diff — JSON of old vs new values
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('http_method', 10)->nullable();

            // Timestamp only (no updated_at — this table is append-only)
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
