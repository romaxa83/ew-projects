<?php

namespace Wezom\Core\Rules;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class YouTubeUrlRuleAttribute extends Rule
{
    public function get(): array
    {
        return [
            new YouTubeUrlRule()
        ];
    }
}
