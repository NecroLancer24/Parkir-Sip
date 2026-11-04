<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class parking_log extends Model
{
    protected $table = 'parking_log';

    protected $primaryKey = 'id_log';

    protected $fillable = ['uuid_pengguna', 'id_slot', 'id_sensor', 'plat_image', 'waktu_masuk', 'waktu_keluar'];

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
}
