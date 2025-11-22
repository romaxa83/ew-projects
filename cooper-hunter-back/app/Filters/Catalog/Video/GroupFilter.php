<?php

namespace App\Filters\Catalog\Video;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class GroupFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

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

    public function active(bool $active): void
    {
        $this->where('active', $active);
    }
}

