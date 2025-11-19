<?php

namespace Wezom\Core\Rules\Seo;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class SeoH1RuleAttribute extends Rule
{
    public function get(): array
    {
        return SeoRules::h1();
    }
}
