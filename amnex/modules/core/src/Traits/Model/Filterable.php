<?php

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Wezom\Core\Dto\FilteringDto;
use Wezom\Core\ModelFilters\ModelFilter;

/**
 * @method Builder|static filter(array|FilteringDto $input = [], $filter = null)
 * @method Builder|static filterWithOrder(FilteringDto $filtering)
 */
trait Filterable
{
    use \EloquentFilter\Filterable {
        scopeFilter as protected traitFilterScope;
    }

    public function scopeFilter(Builder|self $builder, array|FilteringDto $input = [], $filter = null): Builder
    {
        if ($input instanceof FilteringDto) {
            $input = $input->getFilters();
        }

        return $this->traitFilterScope($builder, $input, $filter);
    }

    /**
     * @deprecated Use filter method instead
     */
    public function scopeFilterWithOrder(Builder|self $builder, FilteringDto $filtering): Builder
    {
        return $builder->filter($filtering->getFilters());
    }

    public function provideFilter($filter = null)
    {
        if ($filter === null) {
            $filter = $this->resolveFilterName(get_called_class());
        }

        return $filter;
    }

    /**
     * Method for later filter mention by ide-helper
     */
    public static function newFilter(?string $filter = null, array $input = []): ModelFilter|static
    {
        if ($filter === null) {
            $filter = static::resolveFilterName(get_called_class());
        }

        return new $filter(static::query(), $input);
    }

    public static function resolveFilterName(string $class): string
    {
        return str_replace('\\Models\\', '\\ModelFilters\\', $class . 'Filter');
    }
}
