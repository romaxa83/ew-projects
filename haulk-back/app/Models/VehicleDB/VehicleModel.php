<?php

namespace App\Models\VehicleDB;

use App\ModelFilters\VehicleDB\VehicleModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 * @see VehicleModel::scopeOrderSearchWord($searchWord)
 * @method static Builder|VehicleModel orderSearchWord($searchWord)
 */
class VehicleModel extends Model
{
    use Filterable;

    public const TABLE_NAME = 'vehicle_models';
    public const IMPORT_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformakeid/%d?format=json';

    protected $fillable = [
        'id',
        'make_id',
        'name',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id', 'id');
    }

    /**
     * @return string
     */
    public function modelFilter()
    {
        return $this->provideFilter(VehicleModelFilter::class);
    }

    public function scopeOrderSearchWord(Builder $query, string $searchWord): Builder
    {
        $searchWord = escapeLike(mb_convert_case($searchWord, MB_CASE_LOWER));

        return $query->orderByRaw(
            'lower(name) = \'' . $searchWord . '\' DESC, name ASC',
        );
    }
}
