<?php

use App\Models\parking_log;
use App\Models\sensor;
use App\Models\slot;
use Database\Seeders\ParkingDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the seeder creates seventy-two slots across three floors', function () {
    $this->seed(ParkingDataSeeder::class);

    expect(slot::count())->toBe(72)
        ->and(sensor::count())->toBe(72);

    foreach (['A' => 1, 'B' => 2, 'C' => 3] as $letter => $floor) {
        $floorSlots = slot::query()
            ->where('lokasi', 'Lantai '.$floor)
            ->orderBy('nomer_slot')
            ->get();

        expect($floorSlots->pluck('nomer_slot')->all())->toBe(collect(range(1, 24))
            ->map(fn ($number) => $letter.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT))
            ->all());
    }
});

test('the seeder still produces parking history and active sessions', function () {
    $this->seed(ParkingDataSeeder::class);

    expect(parking_log::count())->toBeGreaterThan(11)
        ->and(parking_log::whereNull('waktu_keluar')->count())->toBeGreaterThan(0);
});
