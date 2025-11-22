<?php

namespace App\GraphQL\Types\Catalog\Features\Values;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Features\Features\FeatureType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Value;

class ValueType extends BaseType
{
    public const NAME = 'FeatureValueType';
    public const MODEL = Value::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id()],
            'sort' => ['type' => NonNullType::int()],
            'active' => ['type' => NonNullType::boolean()],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'feature' => [
                'type' => FeatureType::nonNullType(),
                'is_relation' => true,
            ],
            'metric' => [
                'type' => MetricType::type(),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $fields
        );
    }
}

