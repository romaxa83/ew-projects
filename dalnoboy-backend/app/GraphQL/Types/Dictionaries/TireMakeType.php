<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireMake;

class TireMakeType extends BaseType
{
    public const NAME = 'TireMakeType';
    public const MODEL = TireMake::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
