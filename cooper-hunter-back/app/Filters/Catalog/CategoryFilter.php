<?php

namespace App\Filters\Catalog;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use SlugFilterTrait;

    public function title(string $title): void
    {
        $title = strtolower($title);

        $this->whereHas(
            'translation',
            function (Builder $builder) use ($title) {
                $builder->where(
                    function (Builder $builder) use ($title) {
                        $builder->orWhereRaw('LOWER(`title`) LIKE ?', ["%$title%"]);
                    }
                );
            }
        );
    }

    public function query(string $query): void
    {
        $this->title($query);
    }

    public function active(bool $active): void
    {
        $this->where('active', $active);
    }

    public function parent($parentId): void
    {
        $this->where('parent_id', $parentId);
    }
}

