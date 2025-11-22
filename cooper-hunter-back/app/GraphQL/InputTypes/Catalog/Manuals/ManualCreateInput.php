<?php

namespace App\GraphQL\InputTypes\Catalog\Manuals;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\FileType;
use App\GraphQL\Types\NonNullType;

class ManualCreateInput extends BaseInputType
{
    public const NAME = 'ManualCreateInput';

    public function fields(): array
    {
        return [
            'manual_group_id' => [
                'type' => NonNullType::id(),
            ],
            'pdf' => [
                'type' => FileType::nonNullType(),
            ],
        ];
    }
}
