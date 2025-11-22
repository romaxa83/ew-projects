<?php

namespace App\Filters\Catalog\Video;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class LinkFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function link(string $link): void
    {
        $link = strtolower($link);

        $this->where(function (Builder $builder) use ($link) {
            $builder->orWhereRaw('LOWER(`link`) LIKE ?', ["%$link%"]);
        });
    }

    public function active(bool $active): void
    {
        $this->where('active', $active);
    }

    public function group($groupId): void
    {
        $this->where('group_id', $groupId);
    }

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
}

