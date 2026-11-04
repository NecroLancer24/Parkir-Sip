<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class sensor extends Model
{
    protected $table = 'sensors';

    protected $primaryKey = 'id_sensor';

    protected $fillable = ['plat_nomer', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return HasMany<slot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(slot::class, 'id_sensor', 'id_sensor');
    }

    /**
     * @return HasMany<parking_log, $this>
     */
    public function parkingLogs(): HasMany
    {
        return $this->hasMany(parking_log::class, 'id_sensor', 'id_sensor');
    }

    /**
     * Whether the stored plate value is raw image data from the ESP32-CAM.
     */
    public function hasPlateImage(): bool
    {
        return is_string($this->plat_nomer)
            && $this->plat_nomer !== ''
            && @getimagesizefromstring($this->plat_nomer) !== false;
    }

    /**
     * Public URL for the plate image stored by the ESP32-CAM.
     */
    public function plateImageUrl(): ?string
    {
        if ($this->hasPlateImage()) {
            return route('plate-captures.show', $this);
        }

        if (! is_string($this->plat_nomer) || $this->plat_nomer === '') {
            return null;
        }

        if (preg_match('/\.(jpe?g|png|webp|gif)(\?.*)?$/i', $this->plat_nomer)) {
            if (Str::startsWith($this->plat_nomer, ['http://', 'https://', '/'])) {
                return $this->plat_nomer;
            }

            return route('plate-captures.file', ['path' => ltrim($this->plat_nomer, '/')]);
        }

        return null;
    }

    /**
     * The recognised plate text, when the sensor stored text instead of an image.
     */
    public function plateText(): ?string
    {
        $value = $this->plat_nomer;

        if (! is_string($value) || $value === '' || $this->plateImageUrl() !== null) {
            return null;
        }

        return mb_check_encoding($value, 'UTF-8') ? trim($value) : null;
    }
}
