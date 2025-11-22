<?php

namespace App\Models\Vehicles;

use Carbon\CarbonImmutable;
use Database\Factories\Vehicles\TruckDriverHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null truck_id
 * @property int|null driver_id
 * @property CarbonImmutable assigned_at
 * @property CarbonImmutable|null unassigned_at
 *
 * @method static TruckDriverHistoryFactory factory(...$parameters)
 */
class TruckDriverHistory extends VehicleDriverHistory
{
    use HasFactory;

    public const TABLE_NAME = 'truck_driver_history';

    protected $table = self::TABLE_NAME;

    public function vehicle(): ?BelongsTo
    {
        return $this->belongsTo(Truck::class, 'truck_id');
    }
}
