<?php

namespace App\GraphQL\Types\Settings;

use App\GraphQL\Types\BaseType;
use App\Models\Settings\Settings;
use GraphQL\Type\Definition\Type;

class SettingsType extends BaseType
{
    public const NAME = 'SettingsType';
    public const MODEL = Settings::class;

    public function fields(): array
    {
        return [
            'email' => [
                'type' => Type::string(),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
        ];
    }
}
