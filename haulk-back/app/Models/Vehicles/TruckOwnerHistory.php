<?php

namespace App\Models\Vehicles;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $truck_id
 */
class TruckOwnerHistory extends VehicleOwnerHistory
{
    public const TABLE_NAME = 'truck_owner_history';

    protected $table = self::TABLE_NAME;

    public function vehicle(): ?BelongsTo
    {
        return $this->belongsTo(Truck::class, 'truck_id');
    }
}
