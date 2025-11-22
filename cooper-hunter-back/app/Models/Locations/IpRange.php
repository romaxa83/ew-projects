<?php

namespace App\Models\Locations;

use App\Casts\PointCast;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Locations\IpRangeFactory;

/**
 * @method static IpRangeFactory factory()
 */
class IpRange extends BaseModel
{
    use HasFactory;

    public const TABLE = 'ip_ranges';

    public $timestamps = false;

    protected $fillable = [
        'ip_from',
        'ip_to',
        'state',
        'city',
        'coordinates',
        'zip',
    ];

    protected $casts = [
        'coordinates' => PointCast::class,
    ];
}
