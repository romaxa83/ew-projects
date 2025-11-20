<?php

namespace App\Filters\Admins;

use App\Models\Admins\Admin;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class AdminFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            function (Builder $b) use ($query) {
                    $b
                        ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Admin::TABLE . '.name'), ["%$query%"])
                        ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Admin::TABLE . '.email'), ["%$query%"]);
            }
        );
    }

    protected function allowedOrders(): array
    {
        return Admin::ALLOWED_SORTING_FIELDS;
    }
}
