<?php

namespace App\GraphQL\InputTypes\Favourites;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Favourites\FavouriteModelsEnumType;
use App\GraphQL\Types\NonNullType;

class FavouriteInput extends BaseInputType
{
    public const NAME = 'FavouriteInput';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'type' => [
                'type' => FavouriteModelsEnumType::nonNullType(),
            ],
        ];
    }
}
