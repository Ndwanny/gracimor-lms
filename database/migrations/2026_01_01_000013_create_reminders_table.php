<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tracks every automated or manual reminder sent to borrowers
     * about upcoming or overdue instalments.
     *
     * The scheduled job SendInstalment­Reminders­Job populates this table
     * for the 7-day, 3-day, and 1-day pre-due triggers.
     * Officers can also trigger manual reminders from the UI.
     */
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans');
            $table->foreignId('loan_schedule_id')->constrained('loan_schedule');
            $table->foreignId('borrower_id')->constrained('borrowers');

            // Channel
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'manual_call'])
                  ->default('sms');

            // Trigger
            $table->enum('trigger_type', [
                'pre_due_7_days',
                'pre_due_3_days',
                'pre_due_1_day',
                'due_today',
                'overdue_1_day',
                'overdue_7_days',
                'overdue_14_days',
                'overdue_30_days',
                'manual',
            ])->default('pre_due_3_days');

            // Message content sent
            $table->text('message_body');
            $table->string('recipient_number', 25)->nullable()
                  ->comment('Phone or email address the reminder was sent to');

            // Delivery status
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->string('provider_message_id', 200)->nullable()
                  ->comment('ID returned by the SMS/WhatsApp provider');
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Was this a system or manual trigger?
            $table->boolean('is_automated')->default(true);
            $table->foreignId('triggered_by')->nullable()->constrained('users')
                  ->comment('Null for automated reminders');

            $table->timestamps();

            $table->index('loan_id');
            $table->index('borrower_id');
            $table->index('loan_schedule_id');
            $table->index('channel');
            $table->index('trigger_type');
            $table->index('status');
            $table->index(['loan_schedule_id', 'trigger_type']); // prevent duplicate sends
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
