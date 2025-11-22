<?php

namespace App\Documents\Filters;

use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use Illuminate\Support\Str;

trait HasFilter
{
    /**
     * @param array $filters
     * @return static
     * @throws DocumentFilterMethodNotFoundException
     */
    public static function filter(array $filters): self
    {
        $document = static::query();
        $filter = $document->filterClass();
        /**@var DocumentFilter $filter */
        $filter = new $filter();
        foreach ($filters as $name => $value) {
            $method = Str::camel($name);
            if (!method_exists($filter, $method)) {
                throw new DocumentFilterMethodNotFoundException($method, class_basename($filter));
            }
            $filter->{$method}($value);
        }
        $document->query = $filter->getFilter();
        return $document;
    }
}
