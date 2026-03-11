<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Laravel database notification channel table.
     * Stores in-app notifications for staff users (e.g. "New loan application",
     * "Payment recorded", "Overdue alert"). Uses Laravel's built-in
     * Notification system with the 'database' driver.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Notifiable morphs to User
            $table->string('type');                         // Notification class FQCN
            $table->morphs('notifiable');                  // notifiable_id + notifiable_type

            // The notification payload
            $table->json('data');

            // Null = unread
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
