<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class slot extends Model
{
    protected $table = 'slots';

    protected $primaryKey = 'id_slot';

    protected $fillable = ['id_sensor', 'nomer_slot', 'status_slot', 'lokasi'];

    protected $casts = [
        'status_slot' => 'boolean',
    ];

    /**
     * @return BelongsTo<sensor, $this>
     */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(sensor::class, 'id_sensor', 'id_sensor');
    }

    /**
     * @return HasMany<parking_log, $this>
     */
    public function parkingLogs(): HasMany
    {
        return $this->hasMany(parking_log::class, 'id_slot', 'id_slot');
    }
}
