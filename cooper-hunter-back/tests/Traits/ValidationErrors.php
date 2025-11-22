<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Lang;

trait ValidationErrors
{
    protected function validationError(
        string $rule,
        string $attribute,
        array $args = [],
        string $type = '',
        string $locale = 'uk'
    ): string {
        $rule = $type
            ? 'validation.'.$rule.'.'.$type
            : 'validation.'.$rule;

        $attribute = Lang::has('validation.attributes.'.$attribute)
            ? trans('validation.attributes.'.$attribute, [], $locale)
            : str_replace(['_', '-'], ' ', $attribute);

        $args = array_merge(
            $args,
            ['attribute' => $attribute]
        );

        return trans($rule, $args, $locale);
    }
}
