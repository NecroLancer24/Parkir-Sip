<?php

namespace Database\Seeders;

use App\Models\parking_log;
use App\Models\pengguna;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ParkingDataSeeder extends Seeder
{
    /**
     * Seed sensors, slots, users and parking logs with sample data.
     */
    public function run(): void
    {
        $plates = [
            'B 1234 ABC', 'B 5678 XYZ', 'D 9012 KLM', 'B 3456 NOP',
            'AB 7890 QRS', 'B 2468 RST', 'D 1357 UVW', 'B 8080 HIJ',
            'F 4321 KLM', 'B 1122 MNO', 'D 6789 PQR', 'B 9999 STU',
            'B 1357 ACE', 'D 2468 BDF', 'B 9012 CEG', 'F 5678 DHI',
            'AB 3457 EJK', 'B 7891 FLM', 'D 1123 GNO', 'B 6790 HPQ',
            'F 2469 JRS', 'B 3459 KTW', 'D 8912 LUX', 'B 4567 MVZ',
            'B 2467 NWK', 'D 5681 PLM', 'B 7823 QRS', 'F 9034 RTV',
            'AB 5634 STW', 'B 1275 UXY', 'D 8456 VWZ', 'B 3465 WXA',
            'F 7856 XBC', 'B 9647 YDE', 'D 2378 ZFA', 'B 5189 GHI',
            'B 6621 JKL', 'D 3390 MNP', 'B 4478 QRT', 'F 2234 UVY',
            'AB 8812 WXY', 'B 7745 XYZ', 'D 5567 ABC', 'B 1189 BCD',
            'F 9902 CDE', 'B 6634 DEF', 'D 2256 EFG', 'B 8878 FGH',
            'AB 1290 GHJ', 'B 3345 JKN', 'D 7789 KLM', 'B 5567 LOP',
            'F 6678 MNP', 'B 2211 NPR', 'D 4433 PQR', 'B 8855 QRS',
            'AB 9923 RST', 'B 1146 STV', 'D 6689 TWX', 'B 7734 UXY',
            'F 5591 VWZ', 'B 2289 WXB', 'D 8845 XCD', 'B 9967 YDE',
            'AB 3378 ZFA', 'B 4412 GHI', 'D 5523 IJK', 'B 6634 JKL',
            'F 7745 KLM', 'B 8856 LMN', 'D 9967 MNO', 'B 1178 NOP',
        ];

        $sensors = [];
        foreach ($plates as $plate) {
            $sensors[] = sensor::create([
                'plat_nomer' => $plate,
                'status' => fake()->boolean(30),
            ]);
        }

        // Twenty-four slots per floor in four bands of six: -01..-06, -07..-12,
        // -13..-18, -19..-24. A on Lantai 1, B on 2, C on 3.
        $floorLetters = ['A', 'B', 'C'];
        $slots = [];
        $sensorIndex = 0;

        foreach ($floorLetters as $letterIndex => $letter) {
            foreach (range(1, 24) as $number) {
                $sensorModel = $sensors[$sensorIndex++];

                $slots[] = slot::create([
                    'id_sensor' => $sensorModel->id_sensor,
                    'nomer_slot' => $letter.'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'status_slot' => $sensorModel->status,
                    'lokasi' => 'Lantai '.($letterIndex + 1),
                ]);
            }
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

        // Closed / completed entries, parked for 1-6 hours.
        for ($index = 1; $index <= 40; $index++) {
            $slotModel = collect($slots)->random();
            $arrival = fake()->dateTimeBetween('-14 days', '-7 hours');
            $exit = Carbon::instance($arrival)
                ->addHours(random_int(1, 5))
                ->addMinutes(random_int(0, 55));

            parking_log::create([
                'uuid_pengguna' => collect($penggunas)->random()->uuid_pengguna,
                'id_slot' => $slotModel->id_slot,
                'id_sensor' => $slotModel->id_sensor,
                'jenis_kendaraan' => fake()->randomElement(['motor', 'mobil']),
                'waktu_masuk' => $arrival,
                'waktu_keluar' => $exit,
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
                'jenis_kendaraan' => fake()->randomElement(['motor', 'mobil']),
                'waktu_masuk' => fake()->dateTimeBetween('-6 hours', '-1 hour'),
                'waktu_keluar' => null,
            ]);
        }
    }
}
