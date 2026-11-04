<?php

namespace App\Http\Controllers;

use App\Models\parking_log;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /**
     * Display the parking management dashboard.
     */
    public function index(): View
    {
        $totalSlots = slot::count();
        $occupiedSlots = slot::where('status_slot', true)->count();
        $availableSlots = $totalSlots - $occupiedSlots;
        $activeLogs = parking_log::whereNull('waktu_keluar')->count();

        return view('dashboard', [
            'statistik' => [
                'total_slot' => $totalSlots,
                'slot_terisi' => $occupiedSlots,
                'slot_tersedia' => max($availableSlots, 0),
                'persen_terisi' => $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100) : 0,
            ],
            'slots' => slot::with('sensor')->orderBy('nomer_slot')->get(),
            'riwayatTerbaru' => parking_log::with(['pengguna', 'slot'])
                ->latest('waktu_masuk')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Display a paginated, searchable table of parking logs.
     */
    public function parkingLog(Request $request): View
    {
        $cari = $request->string('q')->trim();

        $riwayatParkir = parking_log::query()
            ->with('sensor')
            ->when($cari, function ($query) use ($cari) {
                $query->where(function ($subQuery) use ($cari) {
                    $subQuery
                        ->where('slots.nomer_slot', 'like', "%{$cari}%")
                        ->orWhere('pengguna.nama', 'like', "%{$cari}%")
                        ->orWhere('pengguna.username', 'like', "%{$cari}%")
                        ->orWhereRaw('CONVERT(sensors.plat_nomer USING utf8mb4) LIKE ?', ["%{$cari}%"]);
                });
            })
            ->join('slots', 'slots.id_slot', '=', 'parking_log.id_slot')
            ->join('pengguna', 'pengguna.uuid_pengguna', '=', 'parking_log.uuid_pengguna')
            ->join('sensors', 'sensors.id_sensor', '=', 'parking_log.id_sensor')
            ->select([
                'parking_log.*',
                'slots.nomer_slot',
                'slots.lokasi',
                'pengguna.nama as nama_pengguna',
            ])
            ->orderBy('slots.nomer_slot')
            ->orderBy('parking_log.waktu_masuk', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('parking-log', [
            'riwayatParkir' => $riwayatParkir,
            'cari' => $cari->value(),
        ]);
    }

    /**
     * Display every recorded field for a single parking log entry.
     */
    public function show(parking_log $log): View
    {
        $log->load(['pengguna', 'slot', 'sensor']);

        return view('parking-log-show', [
            'log' => $log,
        ]);
    }

    /**
     * Stream a plate capture stored on the public disk.
     */
    public function plateFile(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }

    /**
     * Stream the raw plate capture uploaded by the ESP32-CAM.
     */
    public function plateCapture(sensor $sensor): Response
    {
        abort_unless($sensor->hasPlateImage(), 404);

        $image = getimagesizefromstring($sensor->plat_nomer);

        return response($sensor->plat_nomer, 200, [
            'Content-Type' => $image['mime'] ?? 'image/jpeg',
            'Content-Length' => (string) strlen($sensor->plat_nomer),
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /**
     * Store a plate capture uploaded by an ESP32-CAM device.
     */
    public function storePlateCapture(Request $request, parking_log $log): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'plat_nomer' => ['nullable', 'string', 'max:16'],
        ]);

        $path = $request->file('image')->store('plate-captures', 'public');

        abort_if($path === false, 500, 'Unable to store the plate capture.');

        $log->update(['plat_image' => $path]);

        if (isset($validated['plat_nomer'])) {
            $log->sensor?->update(['plat_nomer' => $validated['plat_nomer']]);
        }

        return response()->json([
            'message' => 'Plate capture stored.',
            'id_log' => $log->id_log,
            'plat_image' => $log->fresh()->plateImageUrl(),
        ], 201);
    }
}
