<?php

namespace App\GraphQL\InputTypes\SupportRequests;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class SupportRequestMessageInput extends BaseInputType
{
    public const NAME = 'SupportRequestMessageInput';

    public function fields(): array
    {
        return [
            'text' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'min:2'
                ]
            ],
        ];
    }
}
