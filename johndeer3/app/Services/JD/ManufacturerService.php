<?php

namespace App\Services\JD;

use App\DTO\JD\ManufactureDTO;
use App\Models\JD\Manufacturer;

class ManufacturerService
{
    public function createFromImport(ManufactureDTO $dto) : Manufacturer
    {
        $model = new Manufacturer();
        $model->jd_id = $dto->jdID;
        $model->name = $dto->name;
        $model->status = $dto->status;
        $model->is_partner_jd = $dto->isPartnerJD;
        $model->position = $dto->position;

        $model->save();

        return $model;
    }

    public function updateFromImport(Manufacturer $model, ManufactureDTO $dto) : Manufacturer
    {
        $model->name = $dto->name;
        $model->status = $dto->status;
        $model->is_partner_jd = $dto->isPartnerJD;
        $model->position = $dto->position;

        $model->save();

        return $model;
    }
}
