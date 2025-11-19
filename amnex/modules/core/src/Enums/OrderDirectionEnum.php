<?php

declare(strict_types=1);

namespace Wezom\Core\Enums;

use InvalidArgumentException;

enum OrderDirectionEnum: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';

    public static function fromValue(string $value): OrderDirectionEnum
    {
        $value = mb_strtoupper($value);

        foreach (self::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw new InvalidArgumentException();
    }
}
