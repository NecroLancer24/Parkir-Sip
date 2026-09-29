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
        Schema::create('slots', function (Blueprint $table) {
            $table->id('id_slot')->primary()->autoIncrement();
            $table->foreignId('id_sensor')
                ->constrained('sensors', 'id_sensor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('nomer_slot', 10);
            $table->boolean('status_slot')->default(0);
            $table->string('lokasi', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
