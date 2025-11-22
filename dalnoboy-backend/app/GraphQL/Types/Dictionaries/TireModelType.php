<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireModel;

class TireModelType extends BaseType
{
    public const NAME = 'TireModelType';
    public const MODEL = TireModel::class;

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
                'tire_make' => [
                    'type' => TireMakeType::type(),
                    'is_relation' => true,
                    'alias' => 'tireMake',
                ],
                'is_moderated' => [
                    'type' => NonNullType::boolean(),
                ],
            ]
        );
    }
}
