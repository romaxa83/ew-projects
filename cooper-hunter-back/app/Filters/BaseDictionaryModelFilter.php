<?php

namespace App\Filters;

use App\Traits\Filter\IdFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BaseDictionaryModelFilter
 * @package App\Filters
 *
 * @see ActiveScopeTrait::scopeActive()
 * @method static static active(bool $value = true)
 */
abstract class BaseDictionaryModelFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function query(string $query): void
    {
        $query = '%' . mb_convert_case($query, MB_CASE_LOWER) . '%';

        $this->whereHas(
            'translations',
            fn(Builder $builder) => $builder->whereRaw("LOWER(`title`) LIKE ?", [$query])
                ->orWhereRaw("LOWER(`description`) LIKE ?", [$query])
        );
    }

    public function published(bool $published): void
    {
        $this->where(
            fn(Builder $builder) => $published ? $this->active() : $this->active(false)
        );
    }

}
