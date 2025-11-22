<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\TireChangesReason;

class TireChangesReasonType extends BaseType
{
    public const NAME = 'TireChangesReasonType';
    public const MODEL = TireChangesReason::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'need_description' => [
                'type' => NonNullType::boolean(),
            ],
            'translate' => [
                'type' => TireChangesReasonTranslateType::nonNullType(),
                'is_relation' => true,
            ],
            'translates' => [
                'type' => TireChangesReasonTranslateType::nonNullList(),
                'is_relation' => true,
            ]
        ];
    }
}
