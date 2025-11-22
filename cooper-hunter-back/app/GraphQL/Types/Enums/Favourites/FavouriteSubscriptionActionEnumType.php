<?php

namespace App\GraphQL\Types\Enums\Favourites;

use App\Enums\Favourites\FavouriteSubscriptionActionEnum;
use App\GraphQL\Types\GenericBaseEnumType;

class FavouriteSubscriptionActionEnumType extends GenericBaseEnumType
{
    public const NAME = 'FavouriteSubscriptionActionEnumType';
    public const ENUM_CLASS = FavouriteSubscriptionActionEnum::class;
}
