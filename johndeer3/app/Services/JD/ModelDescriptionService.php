<?php

namespace App\Services\JD;

use App\DTO\JD\ModelDescriptionDTO;
use App\Models\JD\ModelDescription;

class ModelDescriptionService
{
    public function createFromImport(ModelDescriptionDTO $dto) : ModelDescription
    {
        $model = new ModelDescription();
        $model->jd_id = $dto->jdID;
        $model->eg_jd_id = $dto->egID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }

    public function updateFromImport(ModelDescription $model, ModelDescriptionDTO $dto) : ModelDescription
    {
        $model->eg_jd_id = $dto->egID;
        $model->name = $dto->name;
        $model->status = $dto->status;

        $model->save();

        return $model;
    }
}
