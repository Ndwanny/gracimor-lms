<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Replace land_use (enum) with land_type (string) on collateral_assets
        Schema::table('collateral_assets', function (Blueprint $table) {
            $table->string('land_type', 60)->nullable()->after('land_ownership_type');
        });

        DB::statement("UPDATE collateral_assets SET land_type = land_use WHERE land_use IS NOT NULL");

        Schema::table('collateral_assets', function (Blueprint $table) {
            $table->dropColumn('land_use');
        });

        // Add Next of Kin fields to borrowers
        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('nok_name', 150)->nullable()->after('internal_notes');
            $table->string('nok_nrc', 30)->nullable()->after('nok_name');
            $table->string('nok_phone', 20)->nullable()->after('nok_nrc');
            $table->string('nok_email', 150)->nullable()->after('nok_phone');
            $table->text('nok_address')->nullable()->after('nok_email');
            $table->string('nok_relationship', 60)->nullable()->after('nok_address');
        });
    }

    public function down(): void
    {
        Schema::table('collateral_assets', function (Blueprint $table) {
            $table->enum('land_use', ['residential', 'commercial', 'agricultural', 'mixed'])->nullable()->after('land_ownership_type');
        });
        DB::statement("UPDATE collateral_assets SET land_use = land_type WHERE land_type IS NOT NULL");
        Schema::table('collateral_assets', function (Blueprint $table) {
            $table->dropColumn('land_type');
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['nok_name', 'nok_nrc', 'nok_phone', 'nok_email', 'nok_address', 'nok_relationship']);
        });
    }
};
