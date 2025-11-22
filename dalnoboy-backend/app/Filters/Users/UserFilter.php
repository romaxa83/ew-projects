<?php

namespace App\Filters\Users;

use App\Models\Users\User;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends ModelFilter
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
                ->orWhere(
                    fn(Builder $like) => $this->likeRaw($this->getFullNameField(), $query, $like)
                )
                ->orWhereHas(
                    'phones',
                    fn(Builder $like) => $this->likeRaw('phone', $query, $like)
                )
                ->orWhereHas(
                    'branch',
                    fn(Builder $like) => $this->likeRaw('name', $query, $like)
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
        return User::ALLOWED_SORTING_FIELDS;
    }

    protected function allowedOrdersRelations(): array
    {
        return User::ALLOWED_SORTING_FIELDS_RELATIONS;
    }
}
