<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
        
    Route::get('/dashboard/live-data', [DashboardController::class, 'liveData'])
    ->name('dashboard.live-data');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

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

// 1. Menerima foto dari ESP32-CAM
Route::post('/upload-foto', function (Request $request) {
    $dataGambar = $request->getContent();
    if (empty($dataGambar)) {
        $dataGambar = file_get_contents('php://input');
    }

    if (empty($dataGambar)) {
        return response()->json(['status' => 'error', 'message' => 'Data gambar 0 byte'], 400);
    }

    \Illuminate\Support\Facades\Cache::put('heartbeat_esp32cam', now(), now()->addMinutes(15));

    $folderParkir = storage_path('app/public/parkir');
    if (!is_dir($folderParkir)) {
        mkdir($folderParkir, 0777, true);
    }

    $nama_file = 'plat_' . date('Ymd_His') . '.jpg';
    file_put_contents($folderParkir . '/' . $nama_file, $dataGambar);

    // Pilih slot kosong secara acak untuk sementara sampai sensor ultrasonik lantai mendeteksi kendaraan
    $slotKosong = DB::table('slots')->where('status_slot', false)->inRandomOrder()->first()
        ?? DB::table('slots')->inRandomOrder()->first();

    $idSlot = $slotKosong->id_slot ?? 1;
    $idSensor = $slotKosong->id_sensor
        ?? DB::table('sensors')->value('id_sensor')
        ?? 1;
    $uuidPengguna = DB::table('pengguna')->value('uuid_pengguna');

    DB::table('parking_log')->insert([
        'uuid_pengguna' => $uuidPengguna,
        'id_slot'       => $idSlot,
        'id_sensor'     => $idSensor,
        'jenis_kendaraan' => 'motor',
        'plat_image'    => 'parkir/' . $nama_file,
        'waktu_masuk'   => now(),
        'waktu_keluar'  => null,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    return response()->json([
        'status' => 'success',
        'file'   => 'parkir/' . $nama_file,
    ]);
});

// 2. Menerima status 3 sensor ultrasonik dari ESP32-A (?s1=1&s2=0&s3=0)
// Ketiga sensor berada di 1 area parkir (24 slot: A-01 s/d A-24) dengan penempatan acak
Route::match(['get', 'post'], '/update-slot', function (Request $request) {
    $jumlahSensorAktif = (int) $request->input('s1', 0)
        + (int) $request->input('s2', 0)
        + (int) $request->input('s3', 0);

    $slotArea = DB::table('slots')
        ->orderBy('nomer_slot')
        ->limit(24)
        ->get();

    $slotTerisiSekarang = $slotArea->where('status_slot', true)->values();
    $jumlahTerisiLama = $slotTerisiSekarang->count();
    $uuidPengguna = DB::table('pengguna')->value('uuid_pengguna');

    if ($jumlahSensorAktif > $jumlahTerisiLama) {
        // Ada kendaraan baru masuk: pilih slot kosong secara acak
        $selisihMasuk = $jumlahSensorAktif - $jumlahTerisiLama;
        $slotKosongAcak = DB::table('slots')
            ->whereIn('id_slot', $slotArea->pluck('id_slot'))
            ->where('status_slot', false)
            ->inRandomOrder()
            ->limit($selisihMasuk)
            ->get();

        foreach ($slotKosongAcak as $slotBaru) {
            DB::table('slots')
                ->where('id_slot', $slotBaru->id_slot)
                ->update(['status_slot' => true, 'updated_at' => now()]);

            if ($uuidPengguna) {
                DB::table('parking_log')->insert([
                    'uuid_pengguna'   => $uuidPengguna,
                    'id_slot'         => $slotBaru->id_slot,
                    'id_sensor'       => $slotBaru->id_sensor,
                    'jenis_kendaraan' => 'mobil',
                    'waktu_masuk'     => now(),
                    'waktu_keluar'    => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    } elseif ($jumlahSensorAktif < $jumlahTerisiLama) {
        // Ada kendaraan keluar: bebaskan sejumlah slot yang terisi & catat waktu_keluar
        $selisihKeluar = $jumlahTerisiLama - $jumlahSensorAktif;
        $slotKeluar = $slotTerisiSekarang->take($selisihKeluar);

        foreach ($slotKeluar as $slotLama) {
            DB::table('slots')
                ->where('id_slot', $slotLama->id_slot)
                ->update(['status_slot' => false, 'updated_at' => now()]);

            $logAktif = DB::table('parking_log')
                ->where('id_slot', $slotLama->id_slot)
                ->whereNull('waktu_keluar')
                ->orderByDesc('waktu_masuk')
                ->first();

            if ($logAktif) {
                DB::table('parking_log')
                    ->where('id_log', $logAktif->id_log)
                    ->update(['waktu_keluar' => now(), 'updated_at' => now()]);
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'terisi' => $jumlahSensorAktif,
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
})->name('slots.randomize');

// 4. Generate denah penuh 3 lantai (24 slot/lantai = 72 slot) tanpa menghapus riwayat foto
Route::get('/setup-denah-3-lantai', function () {
    if (DB::table('pengguna')->count() === 0) {
        DB::table('pengguna')->insert([
            'uuid_pengguna' => (string) \Illuminate\Support\Str::uuid(),
            'nama'          => 'Pengunjung Umum',
            'username'      => 'guest_parkir',
            'password'      => bcrypt('password'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    $lantaiConfig = [
        'Lantai 1' => 'A',
        'Lantai 2' => 'B',
        'Lantai 3' => 'C',
    ];

    foreach ($lantaiConfig as $namaLantai => $kodeHuruf) {
        for ($i = 1; $i <= 24; $i++) {
            $nomerSlot = $kodeHuruf . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $existing = DB::table('slots')->where('nomer_slot', $nomerSlot)->first();

            if (! $existing) {
                $idSensor = DB::table('sensors')->insertGetId([
                    'plat_nomer' => '-',
                    'status'     => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('slots')->insert([
                    'id_sensor'   => $idSensor,
                    'nomer_slot'  => $nomerSlot,
                    'status_slot' => false,
                    'lokasi'      => $namaLantai,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                DB::table('slots')
                    ->where('id_slot', $existing->id_slot)
                    ->update(['lokasi' => $namaLantai]);
            }
        }
    }

    return redirect()->route('dashboard');
});

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
});