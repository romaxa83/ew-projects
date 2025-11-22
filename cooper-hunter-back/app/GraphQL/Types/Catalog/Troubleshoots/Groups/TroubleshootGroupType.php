<?php

namespace App\GraphQL\Types\Catalog\Troubleshoots\Groups;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots\TroubleshootType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Troubleshoots\Group;

class TroubleshootGroupType extends BaseType
{
    public const NAME = 'TroubleshootGroupType';
    public const MODEL = Group::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'id' => [
                    'type' => NonNullType::id(),
                ],
                'active' => [
                    'type' => NonNullType::boolean()
                ],
                'translation' => [
                    'type' => TranslateType::nonNullType(),
                    'is_relation' => true,
                ],
                'translations' => [
                    'type' => TranslateType::nonNullList(),
                    'is_relation' => true,
                ],
                'troubleshoots' => [
                    'type' => TroubleshootType::list(),
                    'is_relation' => true,
                    'alias' => 'troubleshoots',
                ],
            ]
        );
    }
}
