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

    $this->get(route('dashboard', ['lantai' => 2]))
        ->assertOk()
        ->assertSee(route('dashboard', ['slot' => $slot->id_slot, 'lantai' => 2]));
});

test('the floor plan renders every seeded slot as a linked rectangle', function () {
    $this->actingAs(User::factory()->make());

    foreach (['A-01', 'A-02', 'A-03', 'A-04', 'A-05'] as $index => $nomer) {
        createPreviewSlot(['nomer_slot' => $nomer, 'lokasi' => 'Lantai '.($index % 3 + 1)]);
    }

    // Each floor renders on its own tab, so request all three and count the links.
    $expected = [
        1 => ['A-01', 'A-04'],
        2 => ['A-02', 'A-05'],
        3 => ['A-03'],
    ];

    $totalLinks = 0;

    foreach ($expected as $lantai => $codes) {
        $response = $this->get(route('dashboard', ['lantai' => $lantai]))->assertOk();

        foreach ($codes as $code) {
            $response->assertSee($code);
        }

        $links = substr_count($response->getContent(), '#slot-detail');
        expect($links)->toBe(count($codes));

        $totalLinks += $links;
    }

    expect($totalLinks)->toBe(5);
});

test('the overview shows the floor plan one floor at a time through tabs', function () {
    $this->actingAs(User::factory()->make());

    // Lantai 1: two occupied. Lantai 2: all free. Lantai 3: all occupied.
    $occupancy = [
        'A' => [true, false, true, false, false, false, false, false],
        'B' => [false, false, false, false, false, false, false, false],
        'C' => [true, true, true, true, true, true, true, true],
    ];
    $floorOf = ['A' => 1, 'B' => 2, 'C' => 3];

    foreach ($occupancy as $letter => $states) {
        foreach ($states as $index => $occupied) {
            createPreviewSlot([
                'nomer_slot' => $letter.'-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'lokasi' => 'Lantai '.$floorOf[$letter],
                'status_slot' => $occupied,
            ]);
        }
    }

    $codes = fn (string $letter) => collect(range(1, 8))
        ->map(fn ($number) => $letter.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT))
        ->all();

    $lantai1 = $this->get(route('dashboard', ['lantai' => 1]))
        ->assertOk()
        ->assertSeeInOrder(['Lantai 1', 'Lantai 2', 'Lantai 3']);

    foreach ($codes('A') as $code) {
        $lantai1->assertSee($code);
    }

    $lantai1->assertSee('6 of 8 slots available')->assertDontSee('B-01');

    $this->get(route('dashboard', ['lantai' => 2]))
        ->assertOk()
        ->assertSee('8 of 8 slots available')
        ->assertDontSee('A-01')
        ->assertDontSee('C-01');

    $this->get(route('dashboard', ['lantai' => 3]))
        ->assertOk()
        ->assertSee('0 of 8 slots available')
        ->assertDontSee('B-01');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('A-01')
        ->assertDontSee('B-01');
});

test('selecting a slot opens its own floor and the inline detail below the plan', function () {
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
        ->assertSee('Parking history')
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
