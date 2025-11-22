<?php

namespace App\GraphQL\Types\Catalog\Features\Features;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Metric;
use App\Models\Catalog\Features\Value;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;

class FeaturesForProductInputType extends BaseInputType
{
    public const NAME = 'FeatureForProductInputType';

    public function fields(): array
    {
        return [
            'value_id' => [
                'description' => 'ID значения характеристики',
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'integer',
                    Rule::exists(Value::class, 'id')
                ]
            ],
            'metric_id' => [
                'description' => 'ID значения метрики',
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'integer',
                    Rule::exists(Metric::class, 'id'),
                ]
            ]
        ];
    }
}
