<?php

namespace App\GraphQL\Types\Enums;

use App\GraphQL\Types\BaseEnumType;

class LanguageTypeEnum extends BaseEnumType
{
    public const NAME = 'LanguageTypeEnum';
    public const DESCRIPTION = 'List of project languages';

    public function attributes(): array
    {
        $languages = languages()->pluck('slug');

        $attributes = [
            'values' => []
        ];

        foreach ($languages as $language) {
            $attributes['values'][$language] = $language;
        }

        return array_merge(
            parent::attributes(),
            $attributes
        );
    }
}
