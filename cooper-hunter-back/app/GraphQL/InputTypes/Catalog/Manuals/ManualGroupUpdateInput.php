<?php

namespace App\GraphQL\InputTypes\Catalog\Manuals;

use App\GraphQL\Types\NonNullType;

class ManualGroupUpdateInput extends ManualGroupCreateInput
{
    public const NAME = 'ManualGroupUpdateInput';

    public function fields(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => NonNullType::id(),
                ]
            ],
            parent::fields()
        );
    }
}
