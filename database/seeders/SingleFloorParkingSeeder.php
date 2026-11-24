<?php

namespace Database\Seeders;

use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SingleFloorParkingSeeder extends Seeder
{
    /**
     * Seed one parking floor with 24 available slots.
     */
    public function run(): void
    {
        for ($number = 1; $number <= 24; $number++) {
            $kodeSlot = 'A-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            $existingSlot = slot::query()
                ->where('nomer_slot', $kodeSlot)
                ->first();

            if ($existingSlot !== null) {
                if ($existingSlot->lokasi !== 'Lantai 1') {
                    $existingSlot->update(['lokasi' => 'Lantai 1']);
                }

                continue;
            }

            $sensor = sensor::create([
                'plat_nomer' => '-',
                'status' => false,
            ]);

            slot::create([
                'id_sensor' => $sensor->id_sensor,
                'nomer_slot' => $kodeSlot,
                'status_slot' => false,
                'lokasi' => 'Lantai 1',
            ]);
        }

        if (pengguna::query()->doesntExist()) {
            pengguna::create([
                'uuid_pengguna' => (string) Str::uuid(),
                'nama' => 'Pengunjung Umum',
                'username' => 'guest_parkir',
                'password' => bcrypt('password'),
            ]);
        }
    }
}
