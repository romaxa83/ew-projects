<?php

namespace App\Models\Locations;

use App\Casts\PointCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Locations\ZipcodeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static ZipcodeFactory factory(...$options)
 */
class Zipcode extends BaseModel
{
    use HasFactory;

    public const TABLE = 'zipcodes';

    public $timestamps = false;

    protected $fillable = [
        'state_id',
        'zip',
        'coordinates',
        'name',
        'timezone',
    ];

    protected $casts = [
        'coordinates' => PointCast::class,
    ];

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }
}
