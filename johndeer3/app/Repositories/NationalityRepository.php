<?php

namespace App\Repositories;

use App\Abstractions\AbstractRepository;
use App\Models\User\Nationality;
use Illuminate\Database\Eloquent\Builder;

class NationalityRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Nationality::query();
    }

    // @todo зарефакторить
//    public function getAllWithRequest(array $request)
//    {
//        $perPage = $request['perPage'] ?? Nationality::DEFAULT_PER_PAGE;
//
//        return $this->query()->paginate($perPage);
//    }

//    public function getByName(string $name)
//    {
//        return $this->query()
//            ->where('name', $name)->first();
//    }

    public function getFoSelect()
    {
        return $this->query()
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

//    public function getByNameAndAlias($name, $alias)
//    {
//        return $this->query()
//            ->where('name', $name)
//            ->where('alias', $alias)
//            ->first();
//    }
}
