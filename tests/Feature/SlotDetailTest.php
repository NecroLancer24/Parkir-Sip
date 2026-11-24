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
function createSlotDetailSlot(array $attributes = []): slot
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
function createSlotDetailLog(slot $targetSlot, array $attributes = []): parking_log
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

test('guests are redirected away from the slot detail page', function () {
    $slot = createSlotDetailSlot();

    $this->get(route('slots.show', $slot))
        ->assertRedirect(route('login'));
});

test('the slot detail page shows the slot status and its parking history', function () {
    $this->actingAs(User::factory()->make());

    $slot = createSlotDetailSlot(['nomer_slot' => 'A-07', 'lokasi' => 'Lantai 2']);
    $log = createSlotDetailLog($slot);

    $this->get(route('slots.show', $slot))
        ->assertOk()
        ->assertSee('Slot A-07')
        ->assertSee('Lantai 2')
        ->assertSee('Terisi')
        ->assertSee('B 1234 ABC')
        ->assertSee('Ada Lovelace')
        ->assertSee('Rp 4.000')
        ->assertSee(route('parking-log.show', $log), false);
});

test('the overview renders the inline detail when a slot is selected', function () {
    $this->actingAs(User::factory()->make());

    $slot = createSlotDetailSlot(['nomer_slot' => 'A-07', 'lokasi' => 'Lantai 2']);

    $this->get(route('dashboard', ['slot' => $slot->id_slot]))
        ->assertOk()
        ->assertSee('Slot A-07')
        ->assertSee(route('dashboard', ['slot' => $slot->id_slot]).'#slot-detail', false);
});
