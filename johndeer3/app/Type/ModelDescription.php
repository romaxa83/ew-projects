<?php

namespace App\Type;

final class ModelDescription
{
    const TYPE_ONE = 1;
    const TYPE_TWO = 2;

    private function __construct(){}

    public static function list(): array
    {
        return [
            self::TYPE_ONE => '1',
            self::TYPE_TWO => '2'
        ];
    }
}
