<?php

namespace App\Models\Vehicles;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $trailer_id
 */
class TrailerOwnerHistory extends VehicleOwnerHistory
{
    public const TABLE_NAME = 'trailer_owner_history';

    protected $table = self::TABLE_NAME;

    public function vehicle(): ?BelongsTo
    {
        return $this->belongsTo(Trailer::class, 'trailer_id');
    }
}
