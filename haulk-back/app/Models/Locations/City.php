<?php

namespace App\Models\Locations;

use App\ModelFilters\Locations\CityFilter;
use App\Traits\HelperTrait;
use Database\Factories\Locations\CityFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Locations\City
 *
 * @property int $id
 * @property string $name
 * @property string $zip
 * @property int $status
 * @property int $state_id
 * @property string $timezone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read State $state
 * @method static Builder|static filter($input = [], $filter = null)
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static query()
 * @method static Builder|static simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|static whereId($value)
 * @method static Builder|static where($column, $operator = null, $value = null)
 * @method static Builder|static whereLike($column, $value, $boolean = 'and')
 * @method static Builder|static whereName($value)
 * @method static Builder|static whereStateId($value)
 * @method static Builder|static whereStatus($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @method static Builder|static whereZip($value)
 * @method static static[]|Collection get()
 * @method static static|null first()
 * @mixin Eloquent
 *
 * @method static CityFactory factory(...$parameters)
 */
class City extends Model
{
    use Filterable;
    use HelperTrait;
    use HasFactory;

    public const TABLE_NAME = 'cities';

    public $fillable = [
        'name',
        'status',
        'state_id',
        'zip',
        'timezone',
    ];

    protected $table = 'cities';

    public function modelFilter(): string
    {
        return $this->provideFilter(CityFilter::class);
    }

    public function state(): HasOne
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }
}
