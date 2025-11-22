<?php

namespace App\GraphQL\Types\Catalog\Manuals;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Manuals\ManualGroup;

class ManualGroupType extends BaseType
{
    public const NAME = 'ManualGroupType';
    public const MODEL = ManualGroup::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'show_commercial_certified' => [
                    'type' => NonNullType::boolean(),
                ],
                'translation' => [
                    'type' => ManualGroupTranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => ManualGroupTranslateType::nonNullList(),
                    'is_relation' => true,
                ],
                'manuals' => [
                    'type' => ManualType::list(),
                    'is_relation' => true,
                ],
            ],
        );
    }
}
