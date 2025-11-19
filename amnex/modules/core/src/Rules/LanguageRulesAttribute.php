<?php

namespace Wezom\Core\Rules;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class LanguageRulesAttribute extends Rule
{
    public function get(): array
    {
        return LanguageRules::make();
    }
}
