<?php

namespace App\Filters\Technicians;

use App\Models\Technicians\Technician;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TechnicianFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            function (Builder $builder) use ($query) {
                $builder
                    ->orWhereRaw("LOWER(`technicians`.`first_name`) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(`technicians`.`last_name`) LIKE ?", ["%$query%"])
                    ->orWhereRaw("LOWER(`technicians`.`email`) LIKE ?", ["%$query%"]);
            }
        );
    }

    public function firstName(string $name): void
    {
        $this->where('technicians.first_name', 'like', $name);
    }

    public function customNameSort(string $field, string $direction): void
    {
        $userTable = Technician::TABLE;
        $sortField = "sort_$field";

        $this->selectRaw(
            "CONCAT_WS('',$userTable.last_name,$userTable.first_name) as $sortField",
        )
            ->getQuery()
            ->orderBy($sortField, $direction);
    }

    public function lastName(string $name): void
    {
        $this->where('technicians.last_name', 'like', $name);
    }

    public function email(string $email): void
    {
        $this->where('technicians.email', 'like', $email);
    }

    public function phone(string $phone): void
    {
        $this->where("LOWER(`technicians`.`phone`) LIKE ?", ["%$phone%"]);
    }

    protected function allowedOrders(): array
    {
        return Technician::ALLOWED_SORTING_FIELDS;
    }

    protected function allowedOrdersRelations(): array
    {
        return Technician::ALLOWED_SORTING_FIELDS_RELATIONS;
    }

    protected function orderQuery(string $field, string $direction): void
    {
        if ($field === 'name') {
            $this->orderBy('technicians.last_name', $direction)
                ->orderBy('technicians.first_name', $direction);

            return;
        }

        $this->orderBy($field, $direction);
    }
}
