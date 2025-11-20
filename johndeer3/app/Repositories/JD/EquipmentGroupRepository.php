<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\EquipmentGroup;
use Illuminate\Database\Eloquent\Builder;

class EquipmentGroupRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return EquipmentGroup::query();
    }

//    public function getAllActive()
//    {
//        return $this->query()->active()->with('modelDescriptions')->get();
//    }

    public function getAllForHash()
    {
        return $this->query()->active()
            ->select([
                'jd_id', 'name', 'status'
            ])
            ->with(['modelDescriptions' => function($q){
                return $q->select(['eg_jd_id', 'name', 'status']);
            }])
            ->get()
            ->toArray()
            ;
    }

//    public function getForHash(): Collection
//    {
//        $sub =\DB::table(ModelDescription::TABLE)->select('name');
//        return \DB::table(EquipmentGroup::TABLE)
//            ->select([
//                EquipmentGroup::TABLE.'.*',
////                EquipmentGroup::TABLE.'.jd_id',
////                EquipmentGroup::TABLE.'.name as eg_name',
////                EquipmentGroup::TABLE.'.status as eg_status',
////                ModelDescription::TABLE.'.*',
//                \DB::raw("(SELECT COUNT(*) FROM jd_model_descriptions WHERE jd_model_descriptions.eg_jd_id = jd_equipment_groups.jd_id) as model_descriptions"),
//            ])
////            ->mergeBindings($sub->where('eg_jd_id', 1))
////            ->join(
////                ModelDescription::TABLE,
////                EquipmentGroup::TABLE.'.jd_id',
////                '=',
////                ModelDescription::TABLE.'.eg_jd_id'
////            )
//            ->limit(1)
//            ->get()
//            ;
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
//
//    public function getByName($name)
//    {
//        return $this->query()->where('name', $name)->first();
//    }

    public function getByForStatistic(bool $forStatistic = true, array $relations = [])
    {
        $query = $this->query();

        if(!empty($relations)){
            $query->with($relations);
        }

        return $query->where('for_statistic', $forStatistic)->get();
    }

    public function getAllForName(array $names = [])
    {
        return $this->query()->whereIn('name', $names)->get();
    }
}
