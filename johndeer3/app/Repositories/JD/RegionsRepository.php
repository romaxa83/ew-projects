<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RegionsRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Region::query();
    }

//    public function getAllActive(Request $request)
//    {
//        $perPage = $request['perPage'] ?? Region::DEFAULT_PER_PAGE;
//
//        return $this->query()->active()->paginate($perPage);
//    }

//    public function getAllForAdmin(): ?Collection
//    {
//        return $this->query()->get();
//    }
//
//    public function deleteAll()
//    {
//        return $this->query()->delete();
//    }
//
//    public function existByJdId($jdId)
//    {
//        return $this->query()->where('jd_id', $jdId)->exists();
//    }
//
//    public function getByJdId($jdId)
//    {
//        return $this->query()->where('jd_id', $jdId)->first();
//    }
}
