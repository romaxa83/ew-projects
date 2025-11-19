<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

abstract class BaseEnum extends Enum
{
    public static function listToString(string $delimiter = ', '): string
    {
        return implode($delimiter, self::list());
    }

    public static function list(): array
    {
        return self::getValues();
    }

    public static function ruleIn(): In
    {
        return Rule::in(self::getValues());
    }
}
