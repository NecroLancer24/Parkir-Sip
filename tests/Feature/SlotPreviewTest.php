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
 * Create an occupied or free slot wired to a sensor.
 */
function createPreviewSlot(array $attributes = []): slot
{
    $sensor = sensor::create([
        'plat_nomer' => $attributes['plate'] ?? 'B 1234 ABC',
        'status' => true,
    ]);

    return slot::create([
        'id_sensor' => $sensor->id_sensor,
        'nomer_slot' => $attributes['nomer_slot'] ?? 'A-07',
        'status_slot' => $attributes['status_slot'] ?? true,
        'lokasi' => $attributes['lokasi'] ?? 'Lantai 2',
    ]);
}

/**
 * Create a parking log attached to the given slot.
 */
function createPreviewLog(slot $targetSlot, array $attributes = []): parking_log
{
    $pengguna = pengguna::create([
        'uuid_pengguna' => (string) Str::uuid(),
        'nama' => 'Ada Lovelace',
        'username' => 'ada-'.Str::random(8),
        'password' => bcrypt('password'),
    ]);

    return parking_log::create([
        'uuid_pengguna' => $pengguna->uuid_pengguna,
        'id_slot' => $targetSlot->id_slot,
        'id_sensor' => $targetSlot->id_sensor,
        'jenis_kendaraan' => $attributes['jenis'] ?? 'motor',
        'waktu_masuk' => $attributes['waktu_masuk'] ?? now()->subMinutes(150),
        'waktu_keluar' => $attributes['waktu_keluar'] ?? null,
    ]);
}

test('the overview floor plan links each slot to the inline detail below the preview', function () {
    $this->actingAs(User::factory()->make());

    $slot = createPreviewSlot(['nomer_slot' => 'A-07', 'lokasi' => 'Lantai 2']);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('dashboard', ['slot' => $slot->id_slot]).'#slot-detail', false);
});

test('the parking map renders at most twenty-four linked slots', function () {
    $this->actingAs(User::factory()->make());

    foreach (range(1, 30) as $number) {
        createPreviewSlot([
            'nomer_slot' => 'A-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            'lokasi' => 'Lantai 1',
            'status_slot' => false,
        ]);
    }

    $response = $this->get(route('dashboard'))->assertOk();

    expect(substr_count($response->getContent(), '#slot-detail'))->toBe(24)
        ->and($response->getContent())->toContain('24 tersedia');
});

test('the overview renders one parking area without floor tabs', function () {
    $this->actingAs(User::factory()->make());

    $floorOf = ['A' => 1, 'B' => 2, 'C' => 3];

    foreach ($floorOf as $letter => $floor) {
        foreach (range(1, 8) as $number) {
            createPreviewSlot([
                'nomer_slot' => $letter.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'lokasi' => 'Lantai '.$floor,
                'status_slot' => $number % 2 === 0,
            ]);
        }
    }

    $response = $this->get(route('dashboard', ['lantai' => 2]))->assertOk();

    expect(substr_count($response->getContent(), '#slot-detail'))->toBe(24);

    $response
        ->assertSee('Denah parkir')
        ->assertDontSee('Lantai 1')
        ->assertDontSee('Lantai 2')
        ->assertDontSee('Lantai 3');
});

test('selecting a slot opens its inline detail below the plan', function () {
    $this->actingAs(User::factory()->make());

    createPreviewSlot(['nomer_slot' => 'A-01', 'lokasi' => 'Lantai 1']);

    $slot = createPreviewSlot(['nomer_slot' => 'B-01', 'lokasi' => 'Lantai 2']);
    createPreviewLog($slot);

    $this->get(route('dashboard', ['slot' => $slot->id_slot]))
        ->assertOk()
        ->assertSee('Slot B-01')
        ->assertDontSee('A-01');
});

test('the overview shows the selected slot detail below the floor plan', function () {
    $this->actingAs(User::factory()->make());

    $slot = createPreviewSlot(['nomer_slot' => 'A-07', 'lokasi' => 'Lantai 2']);
    $log = createPreviewLog($slot);

    $this->get(route('dashboard', ['slot' => $slot->id_slot]))
        ->assertOk()
        ->assertSee('Slot A-07')
        ->assertSee('Riwayat parkir')
        ->assertSee('Ada Lovelace')
        ->assertSee('Rp 4.000')
        ->assertSee(route('parking-log.show', $log), false);
});

test('occupied slots render red in the slot preview', function () {
    $this->actingAs(User::factory()->make());

    createPreviewSlot(['nomer_slot' => 'A-01', 'lokasi' => 'Lantai 1', 'status_slot' => true]);
    createPreviewSlot(['nomer_slot' => 'A-02', 'lokasi' => 'Lantai 1', 'status_slot' => false]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('bg-[#fdecec]')
        ->assertSee('bg-[#cd5c5c]');
});

test('available slots keep the green preview color', function () {
    $this->actingAs(User::factory()->make());

    createPreviewSlot(['nomer_slot' => 'A-01', 'lokasi' => 'Lantai 1', 'status_slot' => false]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('bg-[#e8f5f1]')
        ->assertDontSee('bg-[#fdecec]')
        ->assertDontSee('bg-[#edf1f3]');
});

test('the live data endpoint requires authentication', function () {
    $this->get(route('dashboard.live-data'))
        ->assertRedirect(route('login'));
});

test('the live data endpoint returns the current parking area for operators', function () {
    $this->actingAs(User::factory()->make());

    createPreviewSlot(['nomer_slot' => 'A-01', 'lokasi' => 'Lantai 1', 'status_slot' => true]);

    $this->getJson(route('dashboard.live-data'))
        ->assertOk()
        ->assertJsonStructure(['available', 'occupied', 'total', 'percent', 'floor_html'])
        ->assertJson([
            'available' => 0,
            'occupied' => 1,
            'total' => 1,
            'percent' => 100,
        ]);
});
