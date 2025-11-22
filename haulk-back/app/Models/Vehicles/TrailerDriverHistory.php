<?php

namespace App\Models\Vehicles;

use Database\Factories\Vehicles\TrailerDriverHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $trailer_id
 *
 * @method static TrailerDriverHistoryFactory factory(...$parameters)
 */
class TrailerDriverHistory extends VehicleDriverHistory
{
    use HasFactory;

    public const TABLE_NAME = 'trailer_driver_history';

    protected $table = self::TABLE_NAME;

    public function vehicle(): ?BelongsTo
    {
        return $this->belongsTo(Trailer::class, 'trailer_id');
    }
}
