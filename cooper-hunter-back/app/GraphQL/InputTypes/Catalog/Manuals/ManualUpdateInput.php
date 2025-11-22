<?php

namespace App\GraphQL\InputTypes\Catalog\Manuals;

use App\GraphQL\Types\NonNullType;

class ManualUpdateInput extends ManualCreateInput
{
    public const NAME = 'ManualUpdateInput';

    public function fields(): array
    {
        return array_merge(
            [
                'manual_id' => [
                    'type' => NonNullType::id(),
                ],
            ],
            parent::fields(),
        );
    }
}
