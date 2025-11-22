<?php

namespace Core\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;

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

    public static function ruleIn(): string
    {
        return Rule::in(self::getValues());
    }

    public function getTranslation(?string $language = null): ?string
    {
        $key = static::getLocalizationKey() . '.' . $this->value;

        if (!Lang::has($key, $language)) {
            return null;
        }

        return trans(key: $key, locale: $language);
    }
}
