<?php

namespace App\ModelFilters\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Users\User;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class FuelCardFilter extends ModelFilter
{
    public function q(string $name): FuelCardFilter
    {
        return $this->whereRaw('card like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    public function provider(string $provider): FuelCardFilter
    {
        return $this->where('provider', $provider);
    }

    public function driver(int $id): FuelCardFilter
    {
        return $this->whereHas('driver', fn(Builder  $builder) => $builder->where(User::TABLE_NAME . '.id', $id));
    }

    public function status(string $status): FuelCardFilter
    {
        return $this->where('status', $status);
    }

    public function notStatus(string $status): FuelCardFilter
    {
        return $this->where('status', '<>', $status);
    }
}
