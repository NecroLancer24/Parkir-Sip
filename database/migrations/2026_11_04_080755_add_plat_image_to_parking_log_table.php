<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parking_log', function (Blueprint $table) {
            $table->string('plat_image')->nullable()->after('id_sensor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_log', function (Blueprint $table) {
            $table->dropColumn('plat_image');
        });
    }
};
