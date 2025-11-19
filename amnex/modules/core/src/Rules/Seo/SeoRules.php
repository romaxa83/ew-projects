<?php

namespace Wezom\Core\Rules\Seo;

class SeoRules
{
    public static function h1(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    public static function title(): array
    {
        return [
            'nullable',
            'string',
            'max:255',
        ];
    }

    public static function description(): array
    {
        return [
            'nullable',
            'string',
            'max:1000',
        ];
    }

    public static function text(): array
    {
        return [
            'nullable',
            'string',
            'max:5000',
        ];
    }
}
