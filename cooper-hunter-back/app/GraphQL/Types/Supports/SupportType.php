<?php

namespace App\GraphQL\Types\Supports;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Support\Supports\Support;

class SupportType extends BaseType
{
    public const NAME = 'SupportType';
    public const MODEL = Support::class;

    public function fields(): array
    {
        return [
            'phone' => [
                'type' => NonNullType::string(),
            ],
            'translation' => [
                'type' => SupportTranslationType::nonNullType(),
            ],
            'translations' => [
                'type' => SupportTranslationType::nonNullList(),
            ],
        ];
    }
}
