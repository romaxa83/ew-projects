<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\Dealer;
use Illuminate\Database\Eloquent\Builder;

class DealersRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Dealer::query();
    }

//    public function getByName(string $name)
//    {
//        return $this->query()->where('name', $name)->first();
//    }

    public function getAllActive(array $request)
    {
        $perPage = $request['perPage'] ?? Dealer::DEFAULT_PER_PAGE;

        $query = $this->query()->active();

        if(isset($request['name']) && !empty($request['name'])){
            $query = $query->where('name', 'like', $request['name'].'%');
        }

        if(isset($request['country_id']) && !empty($request['country_id'])){
            return $query->where('nationality_id', $request['country_id'])->paginate($perPage);
        }

        if(isset($request['forStatistic']) && filter_var($request['forStatistic'], FILTER_VALIDATE_BOOLEAN)){
            return $query->select(['id', 'name'])->get();
        }

        return $query->paginate($perPage);
    }

    public function getForHash(): \Illuminate\Support\Collection
    {
        return \DB::table(Dealer::TABLE)
            ->select([
                'jd_jd_id',
                'name',
                'country',
                'status',
            ])
            ->get()
            ;
    }

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

//    public function getByJdId($jdId)
//    {
//        return $this->query()->where('jd_id', $jdId)->first();
//    }
}
