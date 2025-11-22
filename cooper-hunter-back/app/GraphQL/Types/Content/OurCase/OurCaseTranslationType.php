<?php

namespace App\GraphQL\Types\Content\OurCase;

use App\GraphQL\Types\BaseTranslationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCaseTranslation;

class OurCaseTranslationType extends BaseTranslationType
{
    public const NAME = 'OurCaseTranslationType';
    public const MODEL = OurCaseTranslation::class;

    public function fields(): array
    {
        return parent::fields() + [
                'title' => [
                    'type' => NonNullType::string(),
                ],
                'description' => [
                    'type' => NonNullType::string(),
                ],
            ];
    }
}
