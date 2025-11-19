<?php

namespace Wezom\Core\Traits\Model;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @method Builder|static searchLike(string $query)
 * @method Builder|static orSearchLike(string $query)
 *
 * @mixin Eloquent
 */
trait SearchScopeTrait
{
    public function scopeSearchLike(Builder $builder, string $value): void
    {
        $value = Str::lower($value);

        $builder->where(function (Builder $b) use ($value) {
            $currentTable = $this->getTable();

            foreach (explode(' ', $value) as $value) {
                foreach (static::getAllowedSearchingFields() as $field) {
                    $b->orWhereRaw(sprintf('LOWER(%s.%s::VARCHAR) like ?', $currentTable, $field), ["%$value%"]);
                }
            }
        });
    }

    public function getAllowedSearchingFields(): array
    {
        return ['name'];
    }

    public function scopeOrSearchLike(Builder $builder, string $value): void
    {
        $builder->orWhere(fn (Builder|self $builder) => $builder->searchLike($value));
    }
}
