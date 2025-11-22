<?php

namespace App\GraphQL\Types\Catalog\Features\Values;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Metric;

class MetricType extends BaseType
{
    public const NAME = 'FeatureMetricType';
    public const MODEL = Metric::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'name' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

