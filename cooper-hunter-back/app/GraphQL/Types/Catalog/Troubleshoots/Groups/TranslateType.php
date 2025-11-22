<?php

namespace App\GraphQL\Types\Catalog\Troubleshoots\Groups;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\GroupTranslation;
use GraphQL\Type\Definition\Type;

class TranslateType extends BaseType
{
    public const NAME = 'TroubleshootGroupTranslateType';
    public const MODEL = GroupTranslation::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'slug' => [
                'type' => NonNullType::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
            'language' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}

