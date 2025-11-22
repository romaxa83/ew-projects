<?php

namespace App\ModelFilters\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Users\User;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class FuelingFilter extends ModelFilter
{
    public function card(string $name): FuelingFilter
    {
        return $this->whereRaw('card like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    public function state(string $name): FuelingFilter
    {
        return $this->whereRaw('LOWER(state) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    public function status(string $status): FuelingFilter
    {
        return $this->where('status', $status);
    }

    public function fuelCardStatus(string $status): FuelingFilter
    {
        return $this->when($status === FuelCardStatusEnum::DELETED, fn(Builder $builder) => $builder->whereNull('fuel_card_id'))
            ->when(
                $status !== FuelCardStatusEnum::DELETED,
                fn(Builder $builder) => $builder
                    ->whereHas('fuelCard', fn(Builder $builder) => $builder->where('status', $status))
            );
    }

    public function driver(int $id): FuelingFilter
    {
        return $this->where('user_id', $id);
    }

    public function source(string $source): FuelingFilter
    {
        return $this->where('source', $source);
    }
}
