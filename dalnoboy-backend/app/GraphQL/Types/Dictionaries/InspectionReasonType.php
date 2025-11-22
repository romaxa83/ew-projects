<?php

namespace App\GraphQL\Types\Dictionaries;

use App\GraphQL\Types\NonNullType;
use App\Models\Dictionaries\InspectionReason;

class InspectionReasonType extends BaseDictionaryType
{
    public const NAME = 'InspectionReasonType';
    public const MODEL = InspectionReason::class;

    protected string $translateTypeClass = InspectionReasonTranslateType::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'need_description' => [
                    'type' => NonNullType::boolean(),
                ],
            ],
        );
    }
}
