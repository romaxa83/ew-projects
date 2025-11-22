<?php

namespace App\Filters\Catalog;

use App\Models\Catalog\Features\Value;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

/**
 * @mixin Value
 */
class ValueFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function title(string $title): void
    {
        $title = strtolower($title);

        $this->whereRaw('LOWER(`title`) LIKE ?', ["%$title%"]);
    }

    public function active(bool $active): void
    {
        $this->where('active', $active);
    }

    public function value(int $valueId): void
    {
        $this->whereKey($valueId);
    }
}
