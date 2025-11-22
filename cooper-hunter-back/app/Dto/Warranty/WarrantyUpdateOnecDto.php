<?php

namespace App\Dto\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;

class WarrantyUpdateOnecDto
{
    public ?string $type;
    public ?string $notice;
    public ?WarrantyStatus $status;

    public ?string $userEmail;
    public ?string $userFirstName;
    public ?string $userLastName;
    public ?string $userCompanyName;
    public ?string $userCompanyAddress;

    public ?string $purchaseDate;
    public ?string $purchasePlace;
    public ?string $installationDate;
    public ?string $installationLicenseNumber;

    public ?string $state;
    public ?string $addressCity;
    public ?string $addressStreet;
    public ?string $addressZip;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->type = data_get($args, 'type');
        $dto->notice = data_get($args, 'notice');
        $dto->status = WarrantyStatus::fromValue(data_get($args, 'warranty_status')) ;

        $dto->userEmail = data_get($args, 'user.email');
        $dto->userFirstName = data_get($args, 'user.first_name');
        $dto->userLastName = data_get($args, 'user.last_name');
        $dto->userCompanyName = data_get($args, 'user.company_name');
        $dto->userCompanyAddress = data_get($args, 'user.company_address');

        $dto->purchaseDate = data_get($args, 'product.purchase_date');
        $dto->purchasePlace = data_get($args, 'product.purchase_place');
        $dto->installationDate = data_get($args, 'product.installation_date');
        $dto->installationLicenseNumber = data_get($args, 'product.installation_license_number');

        $dto->state = data_get($args, 'address.state');
        $dto->addressCity = data_get($args, 'address.city');
        $dto->addressStreet = data_get($args, 'address.street');
        $dto->addressZip = data_get($args, 'address.zip');

        return $dto;
    }
}
