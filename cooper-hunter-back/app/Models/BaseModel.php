<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

/**
 * @method Builder|static select(...$attrs)
 * @method static static|Builder query()
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static static|Builder inRandomOrder()
 * @method static static|Builder create(array $attributes = [])
 *
 * @method null|static|Model first(array $attrs = [])
 * @method Model|static findOrFail($id = null)
 * @method static Model|static with($relations)
 * @method static null|static find($id = null)
 * @method Model|static firstOrFail($columns = ['*'])
 * @method Collection|static[] get(array $columns = [])
 * @method LazyCollection|static[] cursor()
 * @method bool doesntExist()
 *
 * @method static static firstOrCreate(array $attributes = [], array $values = [])
 * @method static static firstOrNew(array $attributes = [], array $values = [])
 *
 * @method orderBy(string $column, string $direction = 'asc')
 *
 * @method Builder|static where($column, $operator = null, $value = null)
 * @method \Illuminate\Support\Collection pluck($column, $key = null)
 *
 * @mixin Model
 */
abstract class BaseModel extends Model
{
    public const DEFAULT_SORT = 0;
    public const DEFAULT_ACTIVE = true;
    public const DEFAULT_PER_PAGE = 15;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->initDefaultAttributes();
    }

    protected function initDefaultAttributes(): void
    {
    }
}
