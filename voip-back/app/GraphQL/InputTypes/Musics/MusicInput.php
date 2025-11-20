<?php

namespace App\GraphQL\InputTypes\Musics;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class MusicInput extends BaseInputType
{
    public const NAME = 'MusicInputType';

    public function fields(): array
    {
        return [
            'interval' => [
                'type' => NonNullType::int(),
            ],
            'active' => [
                'type' => NonNullType::boolean(),
            ],
            'department_id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }
}

