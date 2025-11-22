<?php

namespace App\Dto\Warranty\WarrantyInfo;

use App\Models\Locations\State;
use App\Traits\Dto\CountryIDFromDB;
use Illuminate\Support\Str;

class WarrantyAddressDto
{
    use CountryIDFromDB;

    public $countryID;
    public $stateID;
    public $city;
    public $street;
    public $zip;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->countryID = self::countryIdFromDB($args['country_code']);
        $dto->stateID = $args['state_id'];
//        $dto->stateID = !empty($args['state_id']) ? $args['state_id'] : State::where('slug', Str::slug($args['state']))
//            ->first()->id;
        $dto->city = $args['city'];
        $dto->street = $args['street'];
        $dto->zip = $args['zip'];

        return $dto;
    }
}

