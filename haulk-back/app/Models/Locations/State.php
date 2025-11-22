<?php

namespace App\Models\Locations;

use App\ModelFilters\Locations\StateFilter;
use App\Traits\HelperTrait;
use Illuminate\Support\Carbon;
use Database\Factories\Locations\StateFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Locations\State
 *
 * @property int $id
 * @property string $name
 * @property string $state_short_name
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|City[] $cities
 * @property-read int|null $cities_count
 * @method static Builder|State filter($input = [], $filter = null)
 * @method static Builder|State newModelQuery()
 * @method static Builder|State newQuery()
 * @method static Builder|State where($column, $operator, $value = null)
 * @method static static|null first()
 * @method static Builder|State paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|State query()
 * @method static Collection|State[]|State find(int|int[] $id)
 * @method static Builder|State simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|State whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|State whereCreatedAt($value)
 * @method static Builder|State whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|State whereId($value)
 * @method static Builder|State whereLike($column, $value, $boolean = 'and')
 * @method static Builder|State whereName($value)
 * @method static Builder|State whereStatus($value)
 * @method static Builder|State whereUpdatedAt($value)
 * @mixin Eloquent
 *
 * @method static StateFactory factory(...$parameters)
 */
class State extends Model
{
    use Filterable;
    use HelperTrait;
    use HasFactory;

    public const TABLE_NAME = 'states';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'id',
        'name',
        'status',
        'state_short_name',
        'country_code',
        'country_name'
    ];

    public function modelFilter()
    {
        return $this->provideFilter(StateFilter::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_id', 'id');
    }

    public function getName(): string
    {
        return $this->name;
    }
}
