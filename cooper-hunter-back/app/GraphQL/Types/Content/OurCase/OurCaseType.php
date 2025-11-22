<?php

namespace App\GraphQL\Types\Content\OurCase;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Products\SimpleProductType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCase;

class OurCaseType extends BaseType
{
    public const NAME = 'OurCaseType';
    public const MODEL = OurCase::class;

    public function fields(): array
    {
        return parent::fields() + [
                'sort' => [
                    'type' => NonNullType::int(),
                ],
                'active' => [
                    'type' => NonNullType::boolean(),
                ],
                'image' => [
                    'type' => MediaType::type(),
                    'always' => 'id',
                    'alias' => 'media',
                    'resolve' => fn(OurCase $c) => $c->getFirstMedia($c::MEDIA_COLLECTION_NAME),
                    'deprecationReason' => 'Deprecated due to "images" field becoming multiple',
                ],
                'images' => [
                    'type' => MediaType::list(),
                    'always' => 'id',
                    'alias' => 'media',
                    'resolve' => fn(OurCase $c) => $c->getMedia($c::MEDIA_COLLECTION_NAME),
                ],
                'translation' => [
                    'type' => OurCaseTranslationType::nonNullType(),
                ],
                'translations' => [
                    'type' => OurCaseTranslationType::nonNullList(),
                ],
                'category' => [
                    'type' => OurCaseCategoryType::nonNullType(),
                ],
                'products' => [
                    'type' => SimpleProductType::list(),
                ],
            ];
    }
}
