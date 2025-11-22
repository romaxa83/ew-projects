<?php

namespace App\GraphQL\Types\Enums\Favourites;

use App\Enums\Favourites\FavouriteModelsEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class FavouriteModelsEnumType extends GenericBaseEnumType
{
    public const NAME = 'FavouriteModelsEnumType';
    public const ENUM_CLASS = FavouriteModelsEnum::class;
}
