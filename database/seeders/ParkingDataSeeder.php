<?php

namespace Database\Seeders;

use App\Models\parking_log;
use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParkingDataSeeder extends Seeder
{
    /**
     * Seed sensors, slots, users and parking logs with sample data.
     */
    public function run(): void
    {
        $plates = ['B 1234 ABC', 'B 5678 XYZ', 'D 9012 KLM', 'B 3456 NOP', 'AB 7890 QRS'];

        $sensors = [];
        foreach ($plates as $index => $plate) {
            $sensors[] = sensor::create([
                'plat_nomer' => $plate,
                'status' => fake()->boolean(30),
            ]);
        }

        $slots = [];
        foreach ($sensors as $index => $sensorModel) {
            $slots[] = slot::create([
                'id_sensor' => $sensorModel->id_sensor,
                'nomer_slot' => 'A-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'status_slot' => $sensorModel->status,
                'lokasi' => 'Lantai '.(($index % 3) + 1),
            ]);
        }

        $penggunas = [];
        for ($index = 1; $index <= 5; $index++) {
            $penggunas[] = pengguna::create([
                'uuid_pengguna' => (string) Str::uuid(),
                'nama' => fake()->name(),
                'username' => 'user'.$index,
                'password' => bcrypt('password'),
            ]);
        }

        // Closed / completed entries.
        for ($index = 1; $index <= 40; $index++) {
            $slotModel = collect($slots)->random();

            parking_log::create([
                'uuid_pengguna' => collect($penggunas)->random()->uuid_pengguna,
                'id_slot' => $slotModel->id_slot,
                'id_sensor' => $slotModel->id_sensor,
                'waktu_masuk' => $arrival = fake()->dateTimeBetween('-14 days', '-1 hour'),
                'waktu_keluar' => fake()->dateTimeBetween($arrival, 'now'),
            ]);
        }

        // One active entry per occupied slot.
        foreach ($slots as $slotModel) {
            if (! $slotModel->status_slot) {
                continue;
            }

            parking_log::create([
                'uuid_pengguna' => collect($penggunas)->random()->uuid_pengguna,
                'id_slot' => $slotModel->id_slot,
                'id_sensor' => $slotModel->id_sensor,
                'waktu_masuk' => fake()->dateTimeBetween('-2 hours', '-30 minutes'),
                'waktu_keluar' => null,
            ]);
        }
    }
}
