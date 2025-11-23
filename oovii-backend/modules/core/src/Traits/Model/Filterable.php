<?php

namespace WezomCms\Core\Traits\Model;

use EloquentFilter\Filterable as BaseFilterable;

trait Filterable
{
    use BaseFilterable;

    /**
     * Returns ModelFilter class to be instantiated.
     *
     * @param null|string $filter
     * @return string
     */
    public function provideFilter($filter = null)
    {
        if ($filter === null) {
            // Search in model location directory
            $filter = get_class($this) . 'Filter';

            /** @deprecated Use only ModelFilters directory */
            try {
                $classExists = class_exists($filter);
            } catch (\ErrorException $e) {
                $classExists = false;
            }

            // Search in ModelFilters directory
            if (!$classExists) {
                $filter = str_replace('\\Models\\', '\\ModelFilters\\', $filter);
            }
        }

        return $filter;
    }
}
