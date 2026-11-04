<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class pengguna extends Model
{
    protected $table = 'pengguna';

    protected $primaryKey = 'uuid_pengguna';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['uuid_pengguna', 'nama', 'username', 'password'];

    /**
     * @return HasMany<parking_log, $this>
     */
    public function parkingLogs(): HasMany
    {
        return $this->hasMany(parking_log::class, 'uuid_pengguna', 'uuid_pengguna');
    }
}
