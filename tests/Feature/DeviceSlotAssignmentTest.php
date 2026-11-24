<?php

use App\Models\parking_log;
use App\Models\slot;
use App\Models\User;
use Database\Seeders\SingleFloorParkingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SingleFloorParkingSeeder::class);
    Storage::fake('public');
});

/**
 * Simulate the raw image upload sent by the ESP32-CAM.
 *
 * @param  array<string, mixed>  $parameters
 */
function uploadDevicePhoto(TestCase $test, array $parameters = []): void
{
    $test->call('POST', '/upload-foto', $parameters, [], [], [
        'CONTENT_TYPE' => 'image/jpeg',
        'HTTP_ACCEPT' => 'application/json',
    ], 'fake-jpeg-bytes');
}

test('an esp32-cam upload creates a parking log without a slot', function () {
    uploadDevicePhoto($this, ['jenis_kendaraan' => 'mobil']);

    $log = parking_log::query()->sole();

    expect($log->id_slot)->toBeNull()
        ->and($log->id_sensor)->toBeNull()
        ->and($log->jenis_kendaraan)->toBe('mobil')
        ->and($log->waktu_keluar)->toBeNull()
        ->and($log->plat_image)->not->toBeNull();

    Storage::disk('public')->assertExists($log->plat_image);
});

test('an update-slot assigns the pending vehicle to an empty slot without creating a new log', function () {
    uploadDevicePhoto($this);

    $this->postJson('/update-slot', ['s1' => 1, 's2' => 0, 's3' => 0])
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'sensor_aktif' => 1,
            'terisi' => 1,
            'menunggu_slot' => 0,
        ]);

    $log = parking_log::query()->sole();

    expect($log->id_slot)->not->toBeNull()
        ->and($log->id_sensor)->not->toBeNull()
        ->and($log->waktu_keluar)->toBeNull()
        ->and(slot::query()->where('status_slot', true)->count())->toBe(1)
        ->and(parking_log::query()->count())->toBe(1);
});

test('an update-slot without pending vehicle data does not occupy a slot or create a log', function () {
    $this->postJson('/update-slot', ['s1' => 1, 's2' => 0, 's3' => 0])
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'sensor_aktif' => 1,
            'terisi' => 0,
            'menunggu_slot' => 0,
        ]);

    expect(parking_log::query()->count())->toBe(0)
        ->and(slot::query()->where('status_slot', true)->count())->toBe(0);
});

test('an update-slot does not take over a slot whose vehicle has not exited', function () {
    uploadDevicePhoto($this);
    $this->postJson('/update-slot', ['s1' => 1, 's2' => 0, 's3' => 0])->assertOk();

    $firstLog = parking_log::query()->sole();
    $firstSlotId = $firstLog->id_slot;

    uploadDevicePhoto($this);
    $this->postJson('/update-slot', ['s1' => 1, 's2' => 1, 's3' => 0])->assertOk();

    $firstLog->refresh();
    $secondLog = parking_log::query()->where('id_log', '!=', $firstLog->id_log)->sole();

    expect(parking_log::query()->count())->toBe(2)
        ->and($firstLog->id_slot)->toBe($firstSlotId)
        ->and($firstLog->waktu_keluar)->toBeNull()
        ->and($secondLog->id_slot)->not->toBeNull()
        ->and($secondLog->id_slot)->not->toBe($firstSlotId)
        ->and(slot::query()->where('status_slot', true)->count())->toBe(2);
});

test('an update-slot closes the vehicle log and frees its slot when the vehicle exits', function () {
    uploadDevicePhoto($this);
    $this->postJson('/update-slot', ['s1' => 1, 's2' => 0, 's3' => 0])->assertOk();

    $log = parking_log::query()->sole();

    $this->postJson('/update-slot', ['s1' => 0, 's2' => 0, 's3' => 0])
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'sensor_aktif' => 0,
            'terisi' => 0,
            'menunggu_slot' => 0,
        ]);

    $log->refresh();

    expect($log->waktu_keluar)->not->toBeNull()
        ->and(slot::query()->where('status_slot', true)->count())->toBe(0)
        ->and(parking_log::query()->count())->toBe(1);
});

test('the parking log page shows vehicles that are still waiting for a slot', function () {
    $this->actingAs(User::factory()->make());

    uploadDevicePhoto($this);

    $log = parking_log::query()->sole();

    $this->get(route('parking-log'))
        ->assertOk()
        ->assertSee('Menunggu slot');

    $this->get(route('parking-log.show', $log))
        ->assertOk()
        ->assertSee('Belum ditentukan')
        ->assertSee('Lokasi belum diketahui');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Menunggu update slot dari sensor');
});
