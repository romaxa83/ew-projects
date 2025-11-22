<?php

namespace App\GraphQL\Types\Catalog\Labels;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Labels\LabelTranslation;

class LabelTranslationType extends BaseType
{
    public const NAME = 'LabelTranslationType';
    public const MODEL = LabelTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}



