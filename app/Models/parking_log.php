<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class parking_log extends Model
{
    protected $table = 'parking_log';

    protected $primaryKey = 'id_log';

    protected $fillable = ['uuid_pengguna', 'id_slot', 'id_sensor', 'jenis_kendaraan', 'plat_image', 'waktu_masuk', 'waktu_keluar'];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
    ];

    /**
     * @return BelongsTo<pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(pengguna::class, 'uuid_pengguna', 'uuid_pengguna');
    }

    /**
     * @return BelongsTo<slot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(slot::class, 'id_slot', 'id_slot');
    }

    /**
     * @return BelongsTo<sensor, $this>
     */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(sensor::class, 'id_sensor', 'id_sensor');
    }

    /**
     * Resolve the plate capture image for this entry.
     *
     * A capture can be stored as a path/URL on the log itself, or as raw
     * image bytes captured by the ESP32-CAM in the related sensor row.
     */
    public function plateImageUrl(): ?string
    {
        if (is_string($this->plat_image) && $this->plat_image !== '') {
            if (Str::startsWith($this->plat_image, ['http://', 'https://', '/'])) {
                return $this->plat_image;
            }

            return route('plate-captures.file', ['path' => ltrim($this->plat_image, '/')]);
        }

        return $this->sensor?->plateImageUrl();
    }

    /**
     * The recognised plate text, when the sensor stored text instead of an image.
     */
    public function plateText(): ?string
    {
        return $this->sensor?->plateText();
    }

    /**
     * The billable parking duration in whole hours.
     *
     * A partial hour is billed as a full hour, and the first hour is the
     * minimum. Active entries are billed up to the current time.
     */
    public function durasiTagihan(): int
    {
        if ($this->waktu_masuk === null) {
            return 0;
        }

        $minutes = $this->waktu_masuk->diffInMinutes($this->waktu_keluar ?? now());

        return max(1, (int) ceil($minutes / 60));
    }

    /**
     * The parking tariff in rupiah for this entry.
     *
     * The first hour costs 2k for a motor and 3k for a car; every
     * additional hour adds 1k.
     */
    public function tarifParkir(): int
    {
        $hours = $this->durasiTagihan();

        if ($hours === 0) {
            return 0;
        }

        $firstHour = $this->jenis_kendaraan === 'mobil' ? 3000 : 2000;

        return $firstHour + (($hours - 1) * 1000);
    }

    /**
     * The parking tariff formatted for display.
     */
    public function tarifFormatted(): string
    {
        return 'Rp '.number_format($this->tarifParkir(), 0, ',', '.');
    }

    /**
     * The vehicle type label shown to operators.
     */
    public function jenisKendaraanLabel(): string
    {
        return ucfirst((string) $this->jenis_kendaraan);
    }
}
