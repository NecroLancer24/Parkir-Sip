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
        $availableSlots = $totalSlots - $occupiedSlots;
        $activeLogs = parking_log::whereNull('waktu_keluar')->count();

        $slotTerpilih = $request->filled('slot')
            ? slot::with('sensor')->find($request->integer('slot'))
            : null;

        $riwayatSlot = $slotTerpilih
            ? $slotTerpilih->parkingLogs()->with(['pengguna', 'sensor'])->orderByDesc('waktu_masuk')->get()
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

        // Active floor: explicit ?lantai=, otherwise the floor of the selected slot.
        $activeFloorIndex = $floors->search(
            fn ($floor) => $floor['label'] === 'Lantai '.$request->integer('lantai'),
        );

        if ($activeFloorIndex === false && $slotTerpilih) {
            $activeFloorIndex = $floors->search(
                fn ($floor) => $floor['slots']->contains('id_slot', $slotTerpilih->id_slot),
            );
        }

        $activeFloorIndex = $activeFloorIndex === false ? 0 : $activeFloorIndex;

        return view('dashboard', [
            'statistik' => [
                'total_slot' => $totalSlots,
                'slot_terisi' => $occupiedSlots,
                'slot_tersedia' => max($availableSlots, 0),
                'persen_terisi' => $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100) : 0,
            ],
            'slots' => $slots,
            'floors' => $floors,
            'activeFloor' => $floors->get($activeFloorIndex),
            'activeFloorIndex' => $activeFloorIndex,
            'riwayatTerbaru' => parking_log::with(['pengguna', 'slot'])
                ->latest('waktu_masuk')
                ->limit(5)
                ->get(),
            'slotTerpilih' => $slotTerpilih,
            'riwayatSlot' => $riwayatSlot,
            'logAktifSlot' => $riwayatSlot->firstWhere('waktu_keluar', null),
        ]);
    }

    /**
     * Display a paginated, searchable table of parking logs.
     */
    public function parkingLog(Request $request): View
    {
        $cari = $request->string('q')->trim()->toString();

        return view('parking-log', [
            'riwayatParkir' => $this->parkingLogQuery($cari)->paginate(15)->withQueryString(),
            'cari' => $cari,
        ]);
    }

    /**
     * Download the current parking log search results as an Excel workbook.
     */
    public function exportParkingLog(Request $request): StreamedResponse
    {
        $cari = $request->string('q')->trim()->toString();

        return (new ParkingLogExport($this->parkingLogQuery($cari)->get()))
            ->download('parking-log-'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * Build the searchable parking log query shared by the table and export.
     *
     * @return Builder<parking_log>
     */
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
            ->orderBy('parking_log.waktu_masuk', 'desc');
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
     * Display a single slot with its current status and parking history.
     */
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
