<?php

namespace App\GraphQL\InputTypes\Sips;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;

class SipInput extends BaseInputType
{
    public const NAME = 'SipInputType';

    public function fields(): array
    {
        return [
            'number' => [
                'type' => Type::string(),
            ],
            'password' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
