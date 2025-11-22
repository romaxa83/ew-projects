<?php

namespace App\Filters\Users;

use App\Models\Users\User;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            function (Builder $builder) use ($query) {
                $builder
                    ->orWhereRaw("LOWER(`users`.`first_name`) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(`users`.`last_name`) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(`users`.`email`) LIKE ?", ["%$query%"]);
            }
        );
    }

    public function firstName(string $name): void
    {
        $this->where('first_name', 'like', $name);
    }

    public function customNameSort(string $field, string $direction): void
    {
        $userTable = User::TABLE;
        $sortField = "sort_$field";

        $this->selectRaw(
            "CONCAT_WS('',$userTable.last_name,$userTable.first_name) as $sortField",
        )
            ->getQuery()
            ->orderBy($sortField, $direction);
    }

    public function lastName(string $name): void
    {
        $this->where('last_name', 'like', $name);
    }

    public function email(string $email): void
    {
        $this->where('users.email', 'like', $email);
    }

    public function phone(string $phone): void
    {
        $this->where("LOWER(`users`.`phone`) LIKE ?", ["%$phone%"]);
    }

    protected function allowedOrders(): array
    {
        return User::ALLOWED_SORTING_FIELDS;
    }

    protected function allowedOrdersRelations(): array
    {
        return User::ALLOWED_SORTING_FIELDS_RELATIONS;
    }

    protected function orderQuery(string $field, string $direction): void
    {
        if ($field === 'name') {
            $this->orderBy('last_name', $direction)
                ->orderBy('first_name', $direction);

            return;
        }

        $this->orderBy($field, $direction);
    }
}
