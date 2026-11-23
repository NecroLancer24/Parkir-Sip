<?php

namespace App\Http\Controllers;

use App\Exports\ParkingLogExport;
use App\Models\parking_log;
use App\Models\sensor;
use App\Models\slot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /**
     * Display the parking management dashboard.
     */
    public function index(Request $request): View
    {
        $totalSlots = slot::count();
        $occupiedSlots = slot::where('status_slot', true)->count();
        $availableSlots = max($totalSlots - $occupiedSlots, 0);

        // 1. Statistik Entries Today vs Yesterday
        $entriesToday = parking_log::whereDate('waktu_masuk', today())->count();
        $exitsToday = parking_log::whereDate('waktu_keluar', today())->count();
        $entriesYesterday = parking_log::whereDate('waktu_masuk', today()->subDay())->count();

        if ($entriesYesterday > 0) {
            $diffPercent = round((($entriesToday - $entriesYesterday) / $entriesYesterday) * 100, 1);
        } else {
            $diffPercent = $entriesToday > 0 ? 100.0 : 0.0;
        }

        // 2. Status Connected Devices (ESP32-A, ESP32-B, ESP32-CAM + 3 Sensor Ultrasonik)
        $esp32aOnline = Cache::has('heartbeat_esp32a');
        $esp32camOnline = Cache::has('heartbeat_esp32cam');
        $deviceTotal = 6; // ESP32-A, ESP32-B, ESP32-CAM, Ultrasonik 1, Ultrasonik 2, Ultrasonik 3
        $deviceOnline = ($esp32aOnline ? 5 : 0) + ($esp32camOnline ? 1 : 0);
        $deviceOffline = $deviceTotal - $deviceOnline;

        // 3. Grafik Vehicle Activity per 2 Jam (00:00 - 22:00)
        $hourlyBuckets = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22];
        $entriesSeries = [];
        $exitsSeries = [];

        foreach ($hourlyBuckets as $startHour) {
            $endHour = $startHour + 1;
            $entriesSeries[] = parking_log::whereDate('waktu_masuk', today())
                ->whereRaw('HOUR(waktu_masuk) BETWEEN ? AND ?', [$startHour, $endHour])
                ->count();
            $exitsSeries[] = parking_log::whereDate('waktu_keluar', today())
                ->whereRaw('HOUR(waktu_keluar) BETWEEN ? AND ?', [$startHour, $endHour])
                ->count();
        }

        $maxChartVal = max(10, (int) ceil(max(array_merge($entriesSeries, $exitsSeries, [1])) / 10) * 10);
        $entriesPath = $this->buildSvgLinePath($entriesSeries, $maxChartVal);
        $exitsPath = $this->buildSvgLinePath($exitsSeries, $maxChartVal);

        // 4. Slot & Floor Grouping
        $slotTerpilih = $request->filled('slot')
            ? slot::with('sensor')->find($request->integer('slot'))
            : null;

        $riwayatSlot = $slotTerpilih
            ? $slotTerpilih->parkingLogs()
                ->with(['pengguna', 'sensor'])
                ->orderByDesc('waktu_masuk')
                ->get()
            : collect();

        $slots = slot::with('sensor')->orderBy('nomer_slot')->get();

        $floors = $slots
            ->groupBy(fn (slot $slotModel) => $slotModel->lokasi ?? 'Lantai 1')
            ->sortBy(fn ($floorSlots, $label) => (int) filter_var($label, FILTER_SANITIZE_NUMBER_INT))
            ->map(fn ($floorSlots, $label) => [
                'label' => $label,
                'number' => (int) filter_var($label, FILTER_SANITIZE_NUMBER_INT),
                'slots' => $floorSlots->sortBy('nomer_slot')->values(),
            ])
            ->values();

        $activeFloorIndex = $floors->search(
            fn ($floor) => $floor['label'] === 'Lantai ' . $request->integer('lantai'),
        );

        if ($activeFloorIndex === false && $slotTerpilih) {
            $activeFloorIndex = $floors->search(
                fn ($floor) => $floor['slots']->contains('id_slot', $slotTerpilih->id_slot),
            );
        }

        $activeFloorIndex = $activeFloorIndex === false ? 0 : $activeFloorIndex;

        // 5. Recent Activity Dinamis dari database parking_log
        $recentLogs = parking_log::with(['slot', 'pengguna'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $recentActivities = collect();
        foreach ($recentLogs as $log) {
            $slotCode = $log->slot?->nomer_slot ?? 'Unknown';
            $floorName = $log->slot?->lokasi ?? 'Lantai 1';

            if ($log->waktu_keluar !== null) {
                $recentActivities->push([
                    'icon' => 'gate',
                    'title' => 'Vehicle exited',
                    'meta' => "Gate B · Slot {$slotCode} ({$floorName})",
                    'time' => $log->waktu_keluar->format('H:i'),
                    'timestamp' => $log->waktu_keluar->timestamp,
                    'tone' => 'muted',
                ]);
                $recentActivities->push([
                    'icon' => 'parking',
                    'title' => "Slot {$slotCode} released",
                    'meta' => "Ultrasonik · {$floorName}",
                    'time' => $log->waktu_keluar->format('H:i'),
                    'timestamp' => $log->waktu_keluar->timestamp - 1,
                    'tone' => 'muted',
                ]);
            }

            if ($log->waktu_masuk !== null) {
                $recentActivities->push([
                    'icon' => 'entry',
                    'title' => 'Vehicle entered',
                    'meta' => "Gate A · Slot {$slotCode} ({$floorName})",
                    'time' => $log->waktu_masuk->format('H:i'),
                    'timestamp' => $log->waktu_masuk->timestamp,
                    'tone' => 'brand',
                ]);
                $recentActivities->push([
                    'icon' => 'parking',
                    'title' => "Slot {$slotCode} marked occupied",
                    'meta' => "Ultrasonik · {$floorName}",
                    'time' => $log->waktu_masuk->format('H:i'),
                    'timestamp' => $log->waktu_masuk->timestamp - 1,
                    'tone' => 'brand',
                ]);
            }
        }

        $recentActivities = $recentActivities
            ->sortByDesc('timestamp')
            ->take(5)
            ->values();

        return view('dashboard', [
            'statistik' => [
                'total_slot' => $totalSlots,
                'slot_terisi' => $occupiedSlots,
                'slot_tersedia' => $availableSlots,
                'persen_terisi' => $totalSlots > 0
                    ? round(($occupiedSlots / $totalSlots) * 100)
                    : 0,
                'entries_hari_ini' => $entriesToday,
                'exits_hari_ini' => $exitsToday,
                'perbandingan_kemarin' => $diffPercent,
                'device_online' => $deviceOnline,
                'device_total' => $deviceTotal,
                'device_offline' => $deviceOffline,
            ],
            'chart' => [
                'max_y' => $maxChartVal,
                'entries_path' => $entriesPath,
                'exits_path' => $exitsPath,
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            ],
            'slots' => $slots,
            'floors' => $floors,
            'activeFloor' => $floors->get($activeFloorIndex),
            'activeFloorIndex' => $activeFloorIndex,
            'recentActivities' => $recentActivities,
            'slotTerpilih' => $slotTerpilih,
            'riwayatSlot' => $riwayatSlot,
            'logAktifSlot' => $riwayatSlot->firstWhere('waktu_keluar', null),
        ]);
    }

    /**
     * Build SVG polyline coordinates for the vehicle activity chart.
     *
     * @param  array<int, int>  $values
     */
    private function buildSvgLinePath(array $values, int $maxVal): string
    {
        $count = count($values);
        if ($count === 0) {
            return 'M 0,130 L 640,130';
        }

        $points = [];
        foreach ($values as $idx => $val) {
            $x = round(($idx / max($count - 1, 1)) * 640, 1);
            $ratio = min(max($val / max($maxVal, 1), 0), 1);
            $y = round(126 - ($ratio * 116), 1);
            $points[] = "{$x},{$y}";
        }

        return 'M ' . implode(' L ', $points);
    }

    // --- Method parkingLog, exportParkingLog, parkingLogQuery, show, slotShow, plateFile, plateCapture, storePlateCapture tetap sama seperti sebelumnya ---
    public function parkingLog(Request $request): View
    {
        $cari = $request->string('q')->trim()->toString();

        return view('parking-log', [
            'riwayatParkir' => $this->parkingLogQuery($cari)
                ->paginate(15)
                ->withQueryString(),
            'cari' => $cari,
        ]);
    }

    public function exportParkingLog(Request $request): StreamedResponse
    {
        $cari = $request->string('q')->trim()->toString();

        return (new ParkingLogExport($this->parkingLogQuery($cari)->get()))
            ->download('parking-log-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function parkingLogQuery(string $cari): Builder
    {
        return parking_log::query()
            ->with('sensor')
            ->when($cari !== '', function (Builder $query) use ($cari) {
                $query->where(function (Builder $subQuery) use ($cari) {
                    $subQuery
                        ->where('slots.nomer_slot', 'like', "%{$cari}%")
                        ->orWhere('pengguna.nama', 'like', "%{$cari}%")
                        ->orWhere('pengguna.username', 'like', "%{$cari}%")
                        ->orWhereRaw(
                            'CONVERT(sensors.plat_nomer USING utf8mb4) LIKE ?',
                            ["%{$cari}%"]
                        );
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
            ->orderBy('parking_log.waktu_masuk', 'desc');
    }

    public function show(parking_log $log): View
    {
        $log->load(['pengguna', 'slot', 'sensor']);

        return view('parking-log-show', [
            'log' => $log,
        ]);
    }

    public function slotShow(slot $slot): View
    {
        $slot->load('sensor');

        $logs = $slot->parkingLogs()
            ->with(['pengguna', 'sensor'])
            ->orderByDesc('waktu_masuk')
            ->get();

        return view('slot-show', [
            'slot' => $slot,
            'riwayatSlot' => $logs,
            'logAktif' => $logs->firstWhere('waktu_keluar', null),
        ]);
    }

    public function plateFile(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    }

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