<?php

namespace App\Models\Stores;

use App\Casts\PhoneCast;
use App\Casts\PointCast;
use App\Filters\Stores\DistributorFilter;
use App\Models\BaseHasTranslation;
use App\Models\Locations\State;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\ValueObjects\Point;
use Database\Factories\Stores\DistributorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @see Distributor::scopeAddDistance()
 * @method Builder|self addDistance(Point $coordinates, string $as = 'distance_in_km')
 *
 * @method static DistributorFactory factory()
 */
class Distributor extends BaseHasTranslation
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'distributors';

    public $timestamps = false;

    protected $casts = [
        'coordinates' => PointCast::class,
        'phone' => PhoneCast::class,
    ];

    public function modelFilter(): string
    {
        return DistributorFilter::class;
    }

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function scopeAddDistance(Builder|self $b, Point $coordinates, string $as = 'distance'): void
    {
        $b->addSelect(
            '*',
            DB::raw(
                "(ST_Distance_Sphere(coordinates, ST_GeomFromText('POINT({$coordinates->getLongitude()} {$coordinates->getLatitude()})'))) / 1000 as $as"
            ),
        );
    }
}
