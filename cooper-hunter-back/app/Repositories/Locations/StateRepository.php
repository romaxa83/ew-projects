<?php

namespace App\Repositories\Locations;

use App\Models\Locations\State;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class StateRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return State::query();
    }
}
