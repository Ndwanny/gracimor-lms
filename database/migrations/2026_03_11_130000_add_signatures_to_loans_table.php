<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->text('borrower_signature')->nullable()->after('disburse_notes');
            $table->text('officer_signature')->nullable()->after('borrower_signature');
            $table->timestamp('borrower_signed_at')->nullable()->after('officer_signature');
            $table->timestamp('officer_signed_at')->nullable()->after('borrower_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['borrower_signature', 'officer_signature', 'borrower_signed_at', 'officer_signed_at']);
        });
    }
};
