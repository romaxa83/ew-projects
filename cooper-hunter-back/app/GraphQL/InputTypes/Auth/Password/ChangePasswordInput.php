<?php

namespace App\GraphQL\InputTypes\Auth\Password;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class ChangePasswordInput extends BaseInputType
{
    public const NAME = 'ChangePasswordInput';

    public function fields(): array
    {
        return [
            'current_password' => [
                'type' => NonNullType::string(),
            ],
            'password' => [
                'type' => NonNullType::string(),
            ],
            'password_confirmation' => [
                'type' => NonNullType::string(),
            ]
        ];
    }
}
