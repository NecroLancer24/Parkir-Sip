<?php

use App\Models\parking_log;
use App\Models\sensor;
use App\Models\slot;
use Database\Seeders\ParkingDataSeeder;
use Database\Seeders\SingleFloorParkingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the single floor seeder creates twenty-four available slots on the first floor', function () {
    $this->seed(SingleFloorParkingSeeder::class);

    expect(slot::count())->toBe(24)
        ->and(sensor::count())->toBe(24)
        ->and(slot::where('lokasi', 'Lantai 1')->count())->toBe(24)
        ->and(slot::where('status_slot', true)->count())->toBe(0);

    // Running it again must not duplicate the slots.
    $this->seed(SingleFloorParkingSeeder::class);

    expect(slot::count())->toBe(24)
        ->and(sensor::count())->toBe(24);
});

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
