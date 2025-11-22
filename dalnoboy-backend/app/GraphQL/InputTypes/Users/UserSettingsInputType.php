<?php

namespace App\GraphQL\InputTypes\Users;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Users\AuthorizationExpirationPeriodEnumType;

class UserSettingsInputType extends BaseInputType
{
    public const NAME = 'UserSettingsInputType';

    public function fields(): array
    {
        return [
            'authorization_expiration_period' => [
                'type' => AuthorizationExpirationPeriodEnumType::nonNullType(),
            ],
        ];
    }
}
