<?php

namespace App\Services\Commercial;

use App\Dto\Commercial\CommercialProjectAdditionDto;
use App\Models\Commercial\CommercialProjectAddition;

class CommercialProjectAdditionService
{
    public function create(CommercialProjectAdditionDto $dto): CommercialProjectAddition
    {
        $model = new CommercialProjectAddition();
        $model->commercial_project_id = $dto->commercialProjectID;
        $model->installer_license_number = $dto->installerLicenseNumber;
        $model->purchase_place = $dto->purchasePlace;
        $model->purchase_date = $dto->purchaseDate;
        $model->installation_date = $dto->installationDate;

        $model->save();

        return $model;
    }

    public function update(
        CommercialProjectAddition $model,
        CommercialProjectAdditionDto $dto
    ): CommercialProjectAddition
    {
        $model->installer_license_number = $dto->installerLicenseNumber;
        $model->purchase_place = $dto->purchasePlace;
        $model->purchase_date = $dto->purchaseDate;
        $model->installation_date = $dto->installationDate;

        $model->save();

        return $model;
    }

    public function delete(
        CommercialProjectAddition $model
    ): bool
    {
        return $model->delete();
    }
}

