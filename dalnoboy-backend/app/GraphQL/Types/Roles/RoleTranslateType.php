<?php

namespace App\GraphQL\Types\Roles;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Enums\Localization\LanguageEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Permissions\RoleTranslate;

class RoleTranslateType extends BaseType
{
    public const NAME = 'RoleTranslateType';
    public const MODEL = RoleTranslate::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'title' => [
                'type' => NonNullType::string(),
            ],
            'language' => [
                'type' => LanguageEnumType::nonNullType(),
            ],
        ];
    }
}
