<?php

use App\Models\parking_log;
use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create a parking log with its related sensor, slot, and user.
 */
function createTariffParkingLog(array $attributes = []): parking_log
{
    $sensor = sensor::create([
        'plat_nomer' => $attributes['plate'] ?? 'B 1234 ABC',
        'status' => true,
    ]);

    $slot = slot::create([
        'id_sensor' => $sensor->id_sensor,
        'nomer_slot' => 'A-01',
        'status_slot' => true,
        'lokasi' => 'Lantai 1',
    ]);

    $pengguna = pengguna::create([
        'uuid_pengguna' => (string) Str::uuid(),
        'nama' => 'Ada Lovelace',
        'username' => 'ada-'.Str::random(8),
        'password' => bcrypt('password'),
    ]);

    return parking_log::create([
        'uuid_pengguna' => $pengguna->uuid_pengguna,
        'id_slot' => $slot->id_slot,
        'id_sensor' => $sensor->id_sensor,
        'jenis_kendaraan' => $attributes['jenis'] ?? 'motor',
        'waktu_masuk' => $attributes['waktu_masuk'] ?? now()->subHour(),
        'waktu_keluar' => $attributes['waktu_keluar'] ?? null,
    ]);
}

test('the parking tariff bills the first hour then each additional hour', function () {
    // Motor: 3 hours -> 2k for the first hour + 2k for the next two.
    $motor = createTariffParkingLog([
        'waktu_masuk' => now()->subHours(3),
        'waktu_keluar' => now(),
    ]);
    expect($motor->tarifParkir())->toBe(4000);

    // Mobil: 2.5 hours rounds up to 3 billable hours -> 3k + 2k.
    $mobil = createTariffParkingLog([
        'jenis' => 'mobil',
        'waktu_masuk' => now()->subMinutes(150),
        'waktu_keluar' => now(),
    ]);
    expect($mobil->tarifParkir())->toBe(5000);

    // 45 minutes still bills one full hour.
    $short = createTariffParkingLog([
        'waktu_masuk' => now()->subMinutes(45),
        'waktu_keluar' => now(),
    ]);
    expect($short->tarifParkir())->toBe(2000);
});

test('an active parking log accrues the tariff up to now', function () {
    $log = createTariffParkingLog(['waktu_masuk' => now()->subMinutes(90)]);

    expect($log->tarifParkir())->toBe(3000)
        ->and($log->tarifFormatted())->toBe('Rp 3.000');
});

test('the parking log table shows the vehicle type and tariff', function () {
    $this->actingAs(User::factory()->make());

    createTariffParkingLog([
        'jenis' => 'mobil',
        'waktu_masuk' => now()->subMinutes(150),
        'waktu_keluar' => now(),
    ]);

    $this->get(route('parking-log'))
        ->assertOk()
        ->assertSee('Tarif')
        ->assertSee('Mobil')
        ->assertSee('Rp 5.000');
});

test('the overview shows the parking map with its availability legend', function () {
    $this->actingAs(User::factory()->make());

    createTariffParkingLog();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Denah slot parkir')
        ->assertSee('Denah parkir')
        ->assertSee('Terisi')
        ->assertSee('Tersedia')
        ->assertSee('0 tersedia')
        ->assertDontSee('A-01');
});
