<?php

declare(strict_types=1);

namespace Wezom\Core\Traits;

trait EnumToArrayTrait
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return self::cases();
    }
}
