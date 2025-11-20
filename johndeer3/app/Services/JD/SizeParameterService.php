<?php

namespace App\Services\JD;

use App\DTO\JD\SizeParameterDTO;
use App\Models\JD\SizeParameters;

class SizeParameterService
{
    public function createFromImport(SizeParameterDTO $dto) : SizeParameters
    {
        $model = new SizeParameters();
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }

    public function updateFromImport(SizeParameters $model, SizeParameterDTO $dto) : SizeParameters
    {
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }
}


