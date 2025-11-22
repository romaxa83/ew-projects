<?php

namespace App\Traits\Dto;

use App\Repositories\Locations\CountryRepository;

trait CountryIDFromDB
{
    private static function countryIdFromDB($countryCode): int
    {
        $obj = resolve(CountryRepository::class)->getByFieldsObj(['country_code' => $countryCode], ['id'], true);
        return $obj->id;
    }
}
