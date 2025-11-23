<?php

declare(strict_types=1);

namespace App\Enums\Base;

trait RuleIn
{
    public static function ruleIn()
    {
        $items = array_map(fn($case) => $case->value, self::cases());

        return 'in:' . implode(',', $items);
    }
}

