<?php

namespace App\GraphQL\InputTypes\Auth\Sms;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ConfirmSmsTokenInput extends BaseInputType
{
    public const NAME = 'ConfirmSmsTokenInput';

    public function fields(): array
    {
        return [
            'code' => [
                'type' => NonNullType::string(),
            ],
            'token' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
