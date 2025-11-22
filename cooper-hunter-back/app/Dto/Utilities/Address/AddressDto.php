<?php

namespace App\Dto\Utilities\Address;

use App\Traits\Dto\CountryIDFromDB;

class AddressDto
{
    use CountryIDFromDB;

    public int $stateID;
    public int $countryID;
    public string $city;
    public ?string $addressLine1;
    public ?string $addressLine2;
    public string $zip;
    public ?string $poBox;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->countryID = self::countryIdFromDB(data_get($args, 'country_code'));
        $dto->stateID = data_get($args, 'state_id');
        $dto->city = data_get($args, 'city');
        $dto->addressLine1 = data_get($args, 'address_line_1');
        $dto->addressLine2 = data_get($args, 'address_line_2');
        $dto->zip = data_get($args, 'zip');
        $dto->poBox = data_get($args, 'po_box');

        return $dto;
    }
}


