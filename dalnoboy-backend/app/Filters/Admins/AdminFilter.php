<?php

namespace App\Filters\Admins;

use App\Models\Admins\Admin;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class AdminFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            fn(Builder $builder) => $builder->orWhere(
                fn(Builder $like) => $this->likeRaw('first_name', $query, $like)
            )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('last_name', $query, $like)
                )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('second_name', $query, $like)
                )
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw('email', $query, $like)
                )
                ->orWhereHas(
                    'phones',
                    fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                )
        );
    }

    public function customFullNameSort(string $direction): void
    {
        $adminTable = $this
            ->getModel()
            ->getTable();

        $fields = [
            $adminTable . '.`last_name`',
            $adminTable . '.`first_name`',
            $adminTable . '.`second_name`',
        ];

        $this->orderByRaw("CONCAT_WS(''," . implode(',', $fields) . ") " . $direction);
    }

    protected function allowedOrders(): array
    {
        return Admin::ALLOWED_SORTING_FIELDS;
    }
}
