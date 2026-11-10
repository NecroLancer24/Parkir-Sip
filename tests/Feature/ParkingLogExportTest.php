<?php

use App\Models\parking_log;
use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use ZipArchive;

uses(RefreshDatabase::class);

/**
 * Create a parking log with its related sensor, slot, and user.
 */
function createExportParkingLog(array $attributes = []): parking_log
{
    $sensor = sensor::create([
        'plat_nomer' => $attributes['plate'] ?? 'B 1234 ABC',
        'status' => true,
    ]);

    $slot = slot::create([
        'id_sensor' => $sensor->id_sensor,
        'nomer_slot' => $attributes['slot'] ?? 'A-01',
        'status_slot' => true,
        'lokasi' => $attributes['location'] ?? 'Lantai 1',
    ]);

    $pengguna = pengguna::create([
        'uuid_pengguna' => (string) Str::uuid(),
        'nama' => $attributes['name'] ?? 'Ada Lovelace',
        'username' => $attributes['username'] ?? 'ada',
        'password' => bcrypt('password'),
    ]);

    return parking_log::create([
        'uuid_pengguna' => $pengguna->uuid_pengguna,
        'id_slot' => $slot->id_slot,
        'id_sensor' => $sensor->id_sensor,
        'waktu_masuk' => $attributes['waktu_masuk'] ?? now()->subHour(),
        'waktu_keluar' => $attributes['waktu_keluar'] ?? null,
    ]);
}

/**
 * Read the worksheet XML from a generated xlsx response.
 */
function readExportWorksheet(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'parkir-sip-test-');

    file_put_contents($path, $contents);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBe(true);

    $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    unlink($path);

    return $worksheet;
}

test('guests cannot export the parking log', function () {
    $this->get(route('parking-log.export'))
        ->assertRedirect(route('login'));
});

test('the parking log can be exported as an excel file', function () {
    $this->actingAs(User::factory()->make());

    createExportParkingLog();

    $response = $this->get(route('parking-log.export'));

    $response->assertOk()
        ->assertDownload('parking-log-'.now()->format('Y-m-d').'.xlsx')
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $worksheet = readExportWorksheet($response->streamedContent());

    expect($worksheet)
        ->toContain('Slot')
        ->toContain('B 1234 ABC')
        ->toContain('Ada Lovelace')
        ->toContain('Lantai 1')
        ->toContain('Parked');
});

test('the parking log export respects the search filter', function () {
    $this->actingAs(User::factory()->make());

    createExportParkingLog(['plate' => 'B 1234 ABC', 'slot' => 'A-01']);
    createExportParkingLog([
        'plate' => 'D 9876 XYZ',
        'slot' => 'B-02',
        'name' => 'Grace Hopper',
        'username' => 'grace',
    ]);

    $response = $this->get(route('parking-log.export', ['q' => '9876']));

    $worksheet = readExportWorksheet($response->streamedContent());

    expect($worksheet)
        ->toContain('D 9876 XYZ')
        ->not->toContain('B 1234 ABC');
});

test('the export action is shown on the parking log page, not the overview', function () {
    $this->actingAs(User::factory()->make());

    $this->get(route('parking-log'))
        ->assertOk()
        ->assertSee(route('parking-log.export'), false);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('parking-log.export'), false);
});
