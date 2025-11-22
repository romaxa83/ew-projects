<?php

namespace App\GraphQL\Types\Content\OurCase;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCaseCategory;

class OurCaseCategoryType extends BaseType
{
    public const NAME = 'OurCasesCategoryType';
    public const MODEL = OurCaseCategory::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'sort' => [
                'type' => NonNullType::int(),
            ],
            'image' => [
                'type' => MediaType::type(),
                'always' => 'id',
                'alias' => 'media',
                'resolve' => fn(OurCaseCategory $c) => $c->getFirstMedia($c::MEDIA_COLLECTION_NAME),
            ],
            'cases_count' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => OurCaseCategoryTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => OurCaseCategoryTranslationType::nonNullList(),
            ],
        ];
    }

    protected function resolveCasesCountField(OurCaseCategory $c): string
    {
        $count = $c->cases_count ?? $c->cases()->where('active', true)->count();

        return (string)$count;
    }
}
