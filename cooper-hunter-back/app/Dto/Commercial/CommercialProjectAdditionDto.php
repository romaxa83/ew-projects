<?php

namespace App\Dto\Commercial;

class CommercialProjectAdditionDto
{
    public $commercialProjectID;
    public $purchasePlace;
    public $installerLicenseNumber;
    public $installationDate;
    public $purchaseDate;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->commercialProjectID = $args['commercial_project_id'];
        $dto->purchasePlace = $args['purchase_place'];
        $dto->installerLicenseNumber = $args['installer_license_number'];
        $dto->installationDate = $args['installation_date'];
        $dto->purchaseDate = $args['purchase_date'];

        return $dto;
    }
}

