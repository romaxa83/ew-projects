<?php

namespace App\Filters\Catalog\Troubleshoots;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TroubleshootFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function name(string $name): void
    {
        $name = strtolower($name);

        $this->where(function (Builder $builder) use ($name) {
            $builder->orWhereRaw('LOWER(`name`) LIKE ?', ["%$name%"]);
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
}

