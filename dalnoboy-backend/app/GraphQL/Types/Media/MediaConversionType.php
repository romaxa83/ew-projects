<?php

namespace App\GraphQL\Types\Media;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;

class MediaConversionType extends BaseType
{
    public const NAME = 'MediaConventionType';

    public function fields(): array
    {
        return [
            'convention' => [
                'type' => NonNullType::string(),
            ],
            'url' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
