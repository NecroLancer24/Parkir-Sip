<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
<<<<<<< Updated upstream
use Illuminate\Support\Facades\Route;
=======
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
>>>>>>> Stashed changes

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/live-data', [DashboardController::class, 'liveData'])
        ->name('dashboard.live-data');

    Route::get('/parking-log', [DashboardController::class, 'parkingLog'])
        ->name('parking-log');

    Route::get('/parking-log/export', [DashboardController::class, 'exportParkingLog'])
        ->name('parking-log.export');

    Route::get('/parking-log/{log}', [DashboardController::class, 'show'])
        ->name('parking-log.show');

    Route::get('/slots/{slot}', [DashboardController::class, 'slotShow'])
        ->name('slots.show');
});

Route::get('/plate-captures/file/{path}', [DashboardController::class, 'plateFile'])
    ->where('path', '.*')
    ->name('plate-captures.file');

Route::get('/plate-captures/{sensor}', [DashboardController::class, 'plateCapture'])
    ->name('plate-captures.show');
<<<<<<< Updated upstream
=======

// 1. Menerima data kendaraan (foto) dari ESP32-CAM.
// Slot sengaja dikosongkan dulu dan baru diisi saat ESP32 mengirim update slot.
Route::post('/upload-foto', function (Request $request) {
    $dataGambar = $request->getContent();
    if (empty($dataGambar)) {
        $dataGambar = file_get_contents('php://input');
    }

    if (empty($dataGambar)) {
        return response()->json(['status' => 'error', 'message' => 'Data gambar 0 byte'], 400);
    }

    Cache::put('heartbeat_esp32cam', now(), now()->addMinutes(15));

    $namaFile = 'plat_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.jpg';
    Storage::disk('public')->put('parkir/'.$namaFile, $dataGambar);

    $uuidPengguna = DB::table('pengguna')->value('uuid_pengguna');

    if ($uuidPengguna === null) {
        $uuidPengguna = (string) Str::uuid();

        DB::table('pengguna')->insert([
            'uuid_pengguna' => $uuidPengguna,
            'nama' => 'Pengunjung Umum',
            'username' => 'guest_parkir',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $jenisKendaraan = (string) $request->input('jenis_kendaraan', 'motor');
    if (! in_array($jenisKendaraan, ['motor', 'mobil'], true)) {
        $jenisKendaraan = 'motor';
    }

    $idLog = DB::table('parking_log')->insertGetId([
        'uuid_pengguna' => $uuidPengguna,
        'id_slot' => null,
        'id_sensor' => null,
        'jenis_kendaraan' => $jenisKendaraan,
        'plat_image' => 'parkir/'.$namaFile,
        'waktu_masuk' => now(),
        'waktu_keluar' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'status' => 'success',
        'file' => 'parkir/'.$namaFile,
        'id_log' => $idLog,
        'slot' => null,
        'message' => 'Data kendaraan diterima. Menunggu update slot dari ESP32.',
    ]);
});

// 2. Menerima status 3 sensor ultrasonik dari ESP32-A (?s1=1&s2=0&s3=0)
// Ketiga sensor berada di 1 area parkir (24 slot: A-01 s/d A-24).
Route::match(['get', 'post'], '/update-slot', function (Request $request) {
    $jumlahSensorAktif = max(0, min(24,
        (int) $request->input('s1', 0)
        + (int) $request->input('s2', 0)
        + (int) $request->input('s3', 0)
    ));

    $slotAreaIds = DB::table('slots')
        ->orderBy('nomer_slot')
        ->limit(24)
        ->pluck('id_slot');

    $jumlahTerisiLama = DB::table('slots')
        ->whereIn('id_slot', $slotAreaIds)
        ->where('status_slot', true)
        ->count();

    if ($jumlahSensorAktif > $jumlahTerisiLama) {
        $selisihMasuk = $jumlahSensorAktif - $jumlahTerisiLama;

        // Hanya data kendaraan yang belum memiliki slot yang boleh dipasangkan.
        // Bila tidak ada data kendaraan, jangan buat log atau slot baru.
        $logMenunggu = DB::table('parking_log')
            ->whereNull('id_slot')
            ->whereNull('waktu_keluar')
            ->orderBy('waktu_masuk')
            ->orderBy('id_log')
            ->limit($selisihMasuk)
            ->get();

        $slotKosong = DB::table('slots')
            ->whereIn('id_slot', $slotAreaIds)
            ->where('status_slot', false)
            ->inRandomOrder()
            ->limit($logMenunggu->count())
            ->get();

        DB::transaction(function () use ($logMenunggu, $slotKosong): void {
            foreach ($logMenunggu->values() as $index => $log) {
                $slotBaru = $slotKosong->get($index);

                if ($slotBaru === null) {
                    break;
                }

                DB::table('parking_log')
                    ->where('id_log', $log->id_log)
                    ->update([
                        'id_slot' => $slotBaru->id_slot,
                        'id_sensor' => $slotBaru->id_sensor,
                        'updated_at' => now(),
                    ]);

                DB::table('slots')
                    ->where('id_slot', $slotBaru->id_slot)
                    ->update(['status_slot' => true, 'updated_at' => now()]);
            }
        });
    } elseif ($jumlahSensorAktif < $jumlahTerisiLama) {
        $selisihKeluar = $jumlahTerisiLama - $jumlahSensorAktif;

        // Hanya slot dengan kendaraan aktif yang boleh dikosongkan dan ditutup.
        $logKeluar = DB::table('parking_log')
            ->join('slots', 'slots.id_slot', '=', 'parking_log.id_slot')
            ->whereIn('parking_log.id_slot', $slotAreaIds)
            ->whereNull('parking_log.waktu_keluar')
            ->orderBy('parking_log.waktu_masuk')
            ->orderBy('parking_log.id_log')
            ->limit($selisihKeluar)
            ->select('parking_log.id_log', 'parking_log.id_slot')
            ->get();

        DB::transaction(function () use ($logKeluar): void {
            foreach ($logKeluar as $log) {
                DB::table('parking_log')
                    ->where('id_log', $log->id_log)
                    ->update(['waktu_keluar' => now(), 'updated_at' => now()]);

                $masihAdaKendaraanAktif = DB::table('parking_log')
                    ->where('id_slot', $log->id_slot)
                    ->whereNull('waktu_keluar')
                    ->exists();

                if (! $masihAdaKendaraanAktif) {
                    DB::table('slots')
                        ->where('id_slot', $log->id_slot)
                        ->update(['status_slot' => false, 'updated_at' => now()]);
                }
            }
        });
    }

    $jumlahTerisiSekarang = DB::table('slots')
        ->whereIn('id_slot', $slotAreaIds)
        ->where('status_slot', true)
        ->count();

    $jumlahMenungguSlot = DB::table('parking_log')
        ->whereNull('id_slot')
        ->whereNull('waktu_keluar')
        ->count();

    return response()->json([
        'status' => 'success',
        'sensor_aktif' => $jumlahSensorAktif,
        'terisi' => $jumlahTerisiSekarang,
        'menunggu_slot' => $jumlahMenungguSlot,
    ]);
});

// 3. Acak ulang posisi slot terisi di area 24 slot
Route::get('/randomize-slot', function () {
    $slotIds = DB::table('slots')->orderBy('nomer_slot')->limit(24)->pluck('id_slot');
    $jumlahTerisi = DB::table('slots')->whereIn('id_slot', $slotIds)->where('status_slot', true)->count();

    if ($jumlahTerisi > 0) {
        DB::table('slots')->whereIn('id_slot', $slotIds)->update(['status_slot' => false]);

        $slotBaru = DB::table('slots')
            ->whereIn('id_slot', $slotIds)
            ->inRandomOrder()
            ->limit($jumlahTerisi)
            ->pluck('id_slot');

        DB::table('slots')->whereIn('id_slot', $slotBaru)->update(['status_slot' => true, 'updated_at' => now()]);
    }

    return redirect()->route('dashboard');
})->middleware('auth')->name('slots.randomize');

// 4. Generate denah penuh 3 lantai (24 slot/lantai = 72 slot) tanpa menghapus riwayat foto
Route::get('/setup-denah-3-lantai', function () {
    if (DB::table('pengguna')->count() === 0) {
        DB::table('pengguna')->insert([
            'uuid_pengguna' => (string) Str::uuid(),
            'nama' => 'Pengunjung Umum',
            'username' => 'guest_parkir',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $lantaiConfig = [
        'Lantai 1' => 'A',
        'Lantai 2' => 'B',
        'Lantai 3' => 'C',
    ];

    foreach ($lantaiConfig as $namaLantai => $kodeHuruf) {
        for ($i = 1; $i <= 24; $i++) {
            $nomerSlot = $kodeHuruf.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $existing = DB::table('slots')->where('nomer_slot', $nomerSlot)->first();

            if (! $existing) {
                $idSensor = DB::table('sensors')->insertGetId([
                    'plat_nomer' => '-',
                    'status' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('slots')->insert([
                    'id_sensor' => $idSensor,
                    'nomer_slot' => $nomerSlot,
                    'status_slot' => false,
                    'lokasi' => $namaLantai,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('slots')
                    ->where('id_slot', $existing->id_slot)
                    ->update(['lokasi' => $namaLantai]);
            }
        }
    }

    return redirect()->route('dashboard');
})->middleware('auth');

// 5. Menghapus data & file foto uji coba
Route::get('/hapus-data-coba', function () {
    $jumlahLog = DB::table('parking_log')
        ->where('plat_image', 'like', 'parkir/%')
        ->delete();

    $files = glob(storage_path('app/public/parkir/*'));
    $jumlahFile = 0;
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $jumlahFile++;
            }
        }
    }

    DB::table('slots')->update(['status_slot' => false]);

    return "BERSIH! Berhasil menghapus {$jumlahLog} baris uji coba dan {$jumlahFile} file foto.";
})->middleware('auth');
>>>>>>> Stashed changes
