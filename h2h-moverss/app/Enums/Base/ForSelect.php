<?php

declare(strict_types=1);

namespace App\Enums\Base;

trait ForSelect
{
    public static function forSelect(string $keyName = 'id'): array
    {
        $list = [];
        foreach (static::cases() as $case) {

            $tmp = [
                $keyName => $case->value,
                'title' => $case->labelAsName()
            ];

            if(method_exists($case, 'additionalForSelect')){
                foreach ($case->additionalForSelect() as $field => $values) {
                    $tmp[$field] = $values[$case->value] ?? null;
                }
            }

            $list[] = $tmp;
        }

        return $list;
    }
}

