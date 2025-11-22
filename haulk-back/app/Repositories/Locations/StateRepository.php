<?php

namespace App\Repositories\Locations;

use App\Models\Locations\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StateRepository
{
    /**
     * @var State[]|Collection
     */
    protected static $all;

    /**
     * @return Builder|State
     */
    public function query(): Builder
    {
        return State::query();
    }

    public function findById($id)
    {
        if (is_null(self::$all)) {
            self::$all = $this->query()->get()->keyBy('id');
        }

        return self::$all->get($id);
    }
}
