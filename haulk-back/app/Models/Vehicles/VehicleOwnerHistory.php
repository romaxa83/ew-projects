<?php

namespace App\Models\Vehicles;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $owner_id
 * @property Carbon $assigned_at
 * @property Carbon|null $unassigned_at
 *
 * @see static::owner()
 * @property User|null owner
 *
 * @see static::vehicle()
 * @property Vehicle|null vehicle
 *
 * @mixin Eloquent
 */
abstract class VehicleOwnerHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'unassigned_at' => 'date',
    ];

    public function owner(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    abstract public function vehicle(): ?BelongsTo;
}
