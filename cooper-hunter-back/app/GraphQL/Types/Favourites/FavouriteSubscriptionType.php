<?php

namespace App\GraphQL\Types\Favourites;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Favourites\FavouriteModelsEnumType;
use App\GraphQL\Types\Enums\Favourites\FavouriteSubscriptionActionEnumType;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class FavouriteSubscriptionType extends BaseType
{
    public const NAME = 'FavouriteSubscriptionType';

    public function fields(): array
    {
        return [
            'favourite_id' => [
                'type' => Type::id(),
                'description' => 'If action "deleted_all" there is empty.'
            ],
            'favourite_type' => [
                'type' => FavouriteModelsEnumType::nonNullType(),
                'resolve' => fn(Collection $collection) => mb_convert_case($collection['favourite_type'], MB_CASE_UPPER)
            ],
            'action' => [
                'type' => FavouriteSubscriptionActionEnumType::nonNullType(),
            ],
        ];
    }
}
