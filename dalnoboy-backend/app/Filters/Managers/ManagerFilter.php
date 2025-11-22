<?php

namespace App\Filters\Managers;

use App\Models\Managers\Manager;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class ManagerFilter extends ModelFilter
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
                    fn(Builder $like) => $this->likeRaw($this->getFullNameField(), $query, $like)
                )
                ->orWhereHas(
                    'phones',
                    fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                )
        );
    }

    private function getFullNameField(): string
    {
        $userTable = $this
            ->getModel()
            ->getTable();

        $fields = [
            $userTable . '.`last_name`',
            $userTable . '.`first_name`',
            $userTable . '.`second_name`',
        ];

        return "CONCAT_WS(' '," . implode(',', $fields) . ")";
    }

    public function customFullNameSort(string $direction): void
    {
        $this->orderByRaw($this->getFullNameField() . " " . $direction);
    }

    protected function allowedOrders(): array
    {
        return Manager::ALLOWED_SORTING_FIELDS;
    }
}
