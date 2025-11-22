<?php

namespace App\Models\VehicleDB;

use App\ModelFilters\VehicleDB\VehicleMakeFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/*
 * @see VehicleMake::scopeOrderSearchWord($searchWord)
 * @method static Builder|VehicleMake orderSearchWord($searchWord)
 */
class VehicleMake extends Model
{
    use Filterable;

    public const TABLE_NAME = 'vehicle_makes';
    public const IMPORT_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json';
    public const UPDATE_INTERVAL = 86400 * 30; // 30 days

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = false;

    /**
     * @return string
     */
    public function modelFilter()
    {
        return $this->provideFilter(VehicleMakeFilter::class);
    }

    public function scopeOrderSearchWord(Builder $query, string $searchWord): Builder
    {
        $searchWord = escapeLike(mb_convert_case($searchWord, MB_CASE_LOWER));

        return $query->orderByRaw(
            'lower(name) = \'' . $searchWord . '\' DESC, name ASC',
        );
    }
}
