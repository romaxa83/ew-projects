<?php

namespace App\Foundations\Enums;

class EnumHelper
{
    public static function values($enum): array
    {
        return array_map(fn($case) => $case->value, $enum::cases());
    }

    public static function valuesWithLabel($enum): array
    {
        return array_reduce($enum::cases(), function($result, $case){
            $result[$case->value] = $case->label();
            return $result;
        },[]);
    }

    public static function resourceList($enum): array
    {
        $res = [];
        $data = self::valuesWithLabel($enum);

        foreach ($data as $key => $title){
            $res[] = [
                'key' => $key,
                'title' => $title,
            ];
        }

        return $res;
    }

    public static function ruleIn($enum): string
    {
        return 'in:' . implode(',', static::values($enum));
    }
}
