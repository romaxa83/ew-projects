<?php

namespace App\Repositories;

use App\Models\Hash;

class HashRepository extends AbstractRepository
{
    public function query()
    {
        return Hash::query();
    }

    public function getByAlias(string $alias)
    {
        return $this->query()->where('alias', $alias)->first();
    }
}
