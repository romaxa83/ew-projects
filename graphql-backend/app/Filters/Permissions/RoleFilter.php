<?php

namespace App\Filters\Permissions;

use App\Models\Permissions\Role;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class RoleFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function title(string $title): void
    {
        $title = strtolower($title);

        $this->whereHas(
            'translate',
            function (Builder $builder) use ($title) {
                $builder->where(
                    function (Builder $builder) use ($title) {
                        $builder->orWhereRaw('LOWER(title) LIKE ?', ["%$title%"]);
                    }
                );
            }
        );
    }

    private function allowedOrders(): array
    {
        return Role::ALLOWED_SORTING_FIELDS;
    }

    private function allowedTranslateOrders(): array
    {
        return ['title'];
    }
}
