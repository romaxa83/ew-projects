<?php

namespace App\GraphQL\InputTypes\Settings;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;

class SettingsInputType extends BaseInputType
{
    public const NAME = 'SettingsInputType';

    public function fields(): array
    {
        return [
            'email' => [
                'type' => NonNullType::string(),
                'rules' => [
                    'required',
                    'email',
                ],
            ],
            'phone' => [
                'type' => NonNullType::string(),
            ],
        ];
    }
}
