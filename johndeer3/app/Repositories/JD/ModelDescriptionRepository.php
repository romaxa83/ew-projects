<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\ModelDescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ModelDescriptionRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return ModelDescription::query();
    }

//    public function getAllActive()
//    {
//        return $this->query()->active()->get();
//    }

    public function getForHash(): Collection
    {
        return \DB::table(ModelDescription::TABLE)
            ->select(['eg_jd_id', 'status', 'name'])
            ->get()
            ;
    }

//    public function getAllActiveByEg($egId)
//    {
//        return $this->query()->active()
//            ->where('eg_jd_id', $egId)->get();
//    }

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

//    public function getById($id)
//    {
//        return $this->query()->where('id', $id)->first();
//    }

    public function getForStatistic($egId)
    {
        $query = $this->query()->with(['reportMachine'])
            ->whereHas('reportMachine', function (Builder $q) use($egId) {
                    $q->where('equipment_group_id', $egId);
            })->get();

        return $query;
    }
}
