<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parking_log', function (Blueprint $table) {
            $table->foreignId('id_slot')->nullable()->change();
            $table->foreignId('id_sensor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fallbackSlotId = DB::table('slots')->value('id_slot');
        $fallbackSensorId = DB::table('sensors')->value('id_sensor');

        if ($fallbackSlotId !== null) {
            DB::table('parking_log')
                ->whereNull('id_slot')
                ->update(['id_slot' => $fallbackSlotId]);
        }

        if ($fallbackSensorId !== null) {
            DB::table('parking_log')
                ->whereNull('id_sensor')
                ->update(['id_sensor' => $fallbackSensorId]);
        }

        Schema::table('parking_log', function (Blueprint $table) {
            $table->foreignId('id_slot')->nullable(false)->change();
            $table->foreignId('id_sensor')->nullable(false)->change();
        });
    }
};
