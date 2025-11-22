<?php

namespace App\Models\Vehicles;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $driver_id
 * @property Carbon $assigned_at
 * @property Carbon|null $unassigned_at
 *
 * @see static::driver()
 * @property User|null driver
 *
 * @see static::vehicle()
 * @property Vehicle|null vehicle
 *
 * @mixin Eloquent
 */
abstract class VehicleDriverHistory extends Model
{
    public const ADD_HISTORY_DAYS_PAST = 3;

    public $timestamps = false;

    protected $fillable = [
        'driver_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function driver(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    abstract public function vehicle(): ?BelongsTo;
}
