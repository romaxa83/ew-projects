<?php

namespace App\Services\JD;

use App\DTO\JD\RegionDTO;
use App\Models\JD\Region;

class RegionService
{
    public function createFromImport(RegionDTO $dto) : Region
    {
        $model = new Region();
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }

    public function updateFromImport(Region $model, RegionDTO $dto) : Region
    {
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }
}
