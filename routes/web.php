<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/parking-log', [DashboardController::class, 'parkingLog'])
    ->name('parking-log');

Route::get('/parking-log/{log}', [DashboardController::class, 'show'])
    ->name('parking-log.show');

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

    $folderParkir = storage_path('app/public/parkir');
    if (!is_dir($folderParkir)) {
        mkdir($folderParkir, 0777, true);
    }

    $nama_file = 'plat_' . date('Ymd_His') . '.jpg';
    file_put_contents($folderParkir . '/' . $nama_file, $dataGambar);

    $slotKosong = DB::table('slots')->where('status_slot', false)->orderBy('nomer_slot')->first()
        ?? DB::table('slots')->orderBy('nomer_slot')->first();

    $idSlot = $slotKosong->id_slot ?? 1;
    $idSensor = $slotKosong->id_sensor
        ?? DB::table('sensors')->where('id_sensor', $idSlot)->value('id_sensor')
        ?? DB::table('sensors')->value('id_sensor')
        ?? 1;
    $uuidPengguna = DB::table('pengguna')->value('uuid_pengguna');

    DB::table('parking_log')->insert([
        'uuid_pengguna' => $uuidPengguna,
        'id_slot'       => $idSlot,
        'id_sensor'     => $idSensor,
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
Route::match(['get', 'post'], '/update-slot', function (Request $request) {
    $statusInput = [
        (int) $request->input('s1', 0),
        (int) $request->input('s2', 0),
        (int) $request->input('s3', 0),
    ];

    $daftarSlot = DB::table('slots')->orderBy('nomer_slot')->get();

    foreach ($daftarSlot as $index => $slot) {
        if (!isset($statusInput[$index])) {
            break;
        }

        $terisiBaru = $statusInput[$index] === 1;
        $terisiLama = (bool) $slot->status_slot;

        DB::table('slots')
            ->where('id_slot', $slot->id_slot)
            ->update([
                'status_slot' => $terisiBaru,
                'updated_at'  => now(),
            ]);

        // Jika slot berubah dari terisi (1) menjadi kosong (0), catat waktu_keluar di log aktif
        if ($terisiLama && !$terisiBaru) {
            $logAktif = DB::table('parking_log')
                ->where('id_slot', $slot->id_slot)
                ->whereNull('waktu_keluar')
                ->orderByDesc('waktu_masuk')
                ->first();

            if ($logAktif) {
                DB::table('parking_log')
                    ->where('id_log', $logAktif->id_log)
                    ->update([
                        'waktu_keluar' => now(),
                        'updated_at'   => now(),
                    ]);
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'slots'  => $statusInput,
    ]);
});

// 3. Menghapus data & file foto uji coba
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