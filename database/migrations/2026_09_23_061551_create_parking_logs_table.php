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
        Schema::create('parking_log', function (Blueprint $table) {
            $table->id('id_log')->primary()->autoIncrement();
            $table->foreignUuid('uuid_pengguna')
                ->constrained('pengguna', 'uuid_pengguna')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('id_slot')
                ->constrained('slots', 'id_slot')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('id_sensor')
                ->constrained('sensors', 'id_sensor')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->dateTime('waktu_masuk')->nullable();
            $table->dateTime('waktu_keluar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_log');
    }
};
