<?php

namespace App\GraphQL\Types\Catalog\Features\Features;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Features\Values\ValueType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Traits\GraphQL\HasGuidTrait;

class FeatureType extends BaseType
{
    use HasGuidTrait;

    public const NAME = 'FeatureType';
    public const MODEL = Feature::class;

    public function fields(): array
    {
        $fields = [
            'id' => ['type' => NonNullType::id(),],
            'sort' => ['type' => NonNullType::int()],
            'active' => ['type' => NonNullType::boolean()],
            'display_in_mobile' => [
                'type' => NonNullType::boolean(),
            ],
            'display_in_web' => [
                'type' => NonNullType::boolean(),
            ],
            'display_in_filter' => [
                'type' => NonNullType::boolean(),
            ],
            'values' => [
                'type' => ValueType::list(),
                'is_relation' => true,
            ],
            'translation' => [
                'type' => TranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translations' => [
                'type' => NonNullType::listOf(TranslateType::nonNullType()),
                'is_relation' => true,
            ],
        ];

        return array_merge(
            parent::fields(),
            $this->getGuidField(),
            $fields
        );
    }
}
