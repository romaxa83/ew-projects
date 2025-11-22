<?php

namespace App\Models\Vehicles;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Vehicles\MakeFilter;
use Carbon\CarbonImmutable;
use Database\Factories\Vehicles\MakeFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string name
 * @property CarbonImmutable|null last_updated
 *
 * @see Make::scopeOrderSearchWord()
 * @method static Builder|Make orderSearchWord()
 *
 * @mixin Eloquent
 *
 * @method static MakeFactory factory(...$parameters)
 */
class Make extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'vehicle_makes';
    protected $table = self::TABLE;

    public $timestamps = false;

    /** @var array<int, string> */
    protected $fillable = [
        'id',
        'name',
        'last_updated',
    ];

    /** @var array<int, string> */
    protected $dates = [
        'last_updated'
    ];

    public function modelFilter()
    {
        return $this->provideFilter(MakeFilter::class);
    }

    public function scopeOrderSearchWord(Builder $query, string $searchWord): Builder
    {
        $searchWord = escape_like(mb_convert_case($searchWord, MB_CASE_LOWER));

        return $query->orderByRaw(
            'lower(name) = \'' . $searchWord . '\' DESC, name ASC',
        );
    }
}
