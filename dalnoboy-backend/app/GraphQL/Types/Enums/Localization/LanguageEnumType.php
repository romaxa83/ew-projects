<?php

namespace App\GraphQL\Types\Enums\Localization;

use App\GraphQL\Types\BaseEnumType;

class LanguageEnumType extends BaseEnumType
{
    public const NAME = 'LanguageEnumType';
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
