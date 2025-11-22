<?php

namespace App\Models\Vehicles;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use App\ModelFilters\Vehicles\ModelFilter;
use Database\Factories\Vehicles\ModelFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string name
 * @property int make_id
 *
 * @see self::make()
 * @property BelongsTo|Make make
 *
 * @see Make::scopeOrderSearchWord()
 * @method static Builder|Make orderSearchWord()
 *
 * @mixin Eloquent
 *
 * @method static ModelFactory factory(...$parameters)
 */
class Model extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'vehicle_models';
    protected $table = self::TABLE;

    public $timestamps = false;

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(ModelFilter::class);
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class, 'make_id', 'id');
    }

    public function scopeOrderSearchWord(Builder $query, string $searchWord): Builder
    {
        $searchWord = escape_like(mb_convert_case($searchWord, MB_CASE_LOWER));

        return $query->orderByRaw(
            'lower(name) = \'' . $searchWord . '\' DESC, name ASC',
        );
    }
}
