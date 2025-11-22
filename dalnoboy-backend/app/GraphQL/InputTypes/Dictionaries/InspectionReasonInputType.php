<?php

namespace App\GraphQL\InputTypes\Dictionaries;

use App\GraphQL\Types\NonNullType;

class InspectionReasonInputType extends BaseDictionaryInputType
{
    public const NAME = 'InspectionReasonInputType';

    protected string $translateInputTypeClass = InspectionReasonTranslateInputType::class;

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
