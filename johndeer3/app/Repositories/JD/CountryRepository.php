<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;

class CountryRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Country::query();
    }

//    public function getAll(array $request = [])
//    {
//        $perPage = $request['perPage'] ?? Country::DEFAULT_PER_PAGE;
//
//        $query = $this->query();
//
//        if(isset($request['isActive'])){
//            $query->where('active', true);
//        }
//
//        // фильтр по name (Имя Фамилия)
//
//        return $query->paginate($perPage);
//    }

}
