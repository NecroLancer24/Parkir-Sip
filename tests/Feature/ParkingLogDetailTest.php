<?php

use App\Models\parking_log;
use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Create a parking log with its related sensor, slot, and user.
 */
function createParkingLog(array $attributes = []): parking_log
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
        'username' => 'ada',
        'password' => bcrypt('password'),
    ]);

    return parking_log::create([
        'uuid_pengguna' => $pengguna->uuid_pengguna,
        'id_slot' => $slot->id_slot,
        'id_sensor' => $sensor->id_sensor,
        'plat_image' => $attributes['plat_image'] ?? null,
        'waktu_masuk' => now()->subHour(),
        'waktu_keluar' => $attributes['waktu_keluar'] ?? null,
    ]);
}

test('the parking log table links each row to its detail page', function () {
    $log = createParkingLog();

    $this->get(route('parking-log'))
        ->assertOk()
        ->assertSee(route('parking-log.show', $log), false)
        ->assertSee('B 1234 ABC');
});

test('the parking log detail page shows the record fields and plate capture', function () {
    $log = createParkingLog([
        'plat_image' => 'plate-captures/capture.jpg',
        'waktu_keluar' => now(),
    ]);

    $this->get(route('parking-log.show', $log))
        ->assertOk()
        ->assertSee('Log #'.$log->id_log)
        ->assertSee('Ada Lovelace')
        ->assertSee('ada')
        ->assertSee('A-01')
        ->assertSee('Lantai 1')
        ->assertSee('B 1234 ABC')
        ->assertSee($log->plateImageUrl(), false);
});

test('the plate capture route streams an image stored on the public disk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('plate-captures/capture.jpg', 'fake-image');

    $log = createParkingLog(['plat_image' => 'plate-captures/capture.jpg']);

    $this->get(route('plate-captures.file', ['path' => 'plate-captures/capture.jpg']))
        ->assertOk();
});

test('the plate capture route streams raw sensor image bytes', function () {
    $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

    $sensor = sensor::create([
        'plat_nomer' => $gif,
        'status' => false,
    ]);

    $this->get(route('plate-captures.show', $sensor))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/gif');
});

test('an esp32-cam can upload a plate capture for a parking log', function () {
    Storage::fake('public');

    $log = createParkingLog();

    $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

    $response = $this->postJson(route('api.parking-log.plate-capture', $log), [
        'image' => UploadedFile::fake()->createWithContent('capture.gif', $gif),
    ]);

    $response->assertCreated();

    $log->refresh();

    expect($log->plat_image)->not->toBeNull();
    Storage::disk('public')->assertExists($log->plat_image);
});
