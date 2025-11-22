<?php

namespace App\GraphQL\Types\Catalog\Manuals;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\ManualGroupTranslation;

class ManualGroupTranslateType extends BaseType
{
    public const NAME = 'ManualGroupTranslateType';
    public const MODEL = ManualGroupTranslation::class;

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
