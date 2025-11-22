<?php

namespace App\Models\Vehicles;

use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Vehicles\TrailerFilter;
use Database\Factories\Vehicles\TrailerFactory;
use Eloquent;

/**
 * @mixin Eloquent
 *
 * @method static TrailerFactory factory(...$parameters)
 */
class Trailer extends Vehicle
{
    use Filterable;

    public const TABLE = 'vehicle_trailers';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'trailer';

    public function modelFilter()
    {
        return $this->provideFilter(TrailerFilter::class);
    }
}
