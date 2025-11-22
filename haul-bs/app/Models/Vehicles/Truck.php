<?php

namespace App\Models\Vehicles;

use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Vehicles\TruckFilter;
use Database\Factories\Vehicles\TruckFactory;
use Eloquent;

/**
 * @mixin Eloquent
 *
 * @method static TruckFactory factory(...$parameters)
 */
class Truck extends Vehicle
{
    use Filterable;

    public const TABLE = 'vehicle_trucks';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'truck';

    public function modelFilter()
    {
        return $this->provideFilter(TruckFilter::class);
    }
}
