<?php

namespace App\Services\JD;

use App\DTO\JD\EquipmentGroupDTO;
use App\Models\JD\EquipmentGroup;

class EquipmentGroupService
{
    public function createFromImport(EquipmentGroupDTO $dto) : EquipmentGroup
    {
        $model = new EquipmentGroup();
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;
        $model->for_statistic = $dto->forStatistic;

        $model->save();

        return $model;
    }

    public function updateFromImport(EquipmentGroup $model, EquipmentGroupDTO $dto) : EquipmentGroup
    {
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }

    public function attachEgs(EquipmentGroup $eg, array $egIds = []): EquipmentGroup
    {
        $eg->relatedEgs()->detach();
        $eg->relatedEgs()->attach($egIds);

        return $eg;
    }
}
