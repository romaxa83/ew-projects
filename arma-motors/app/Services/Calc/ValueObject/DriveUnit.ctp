<?php

namespace App\Services\Calc\ValueObject;

use App\Services\Calc\Exception\BrandCalcException;

final class DriveUnit
{
    const _RWD = 1;     // задний привод
    const _FWD = 2;     // передний привод
    const _4WD = 3;     // полный привод
    const _AWD = 4;     // all-wheel drive

    private int $type;

    private function __construct(){}

    public static function list()
    {
        return [
            self::_RWD => 'rwd',
            self::_FWD => 'fwd',
            self::_4WD => '4wd',
            self::_AWD => 'awd',
        ];
    }

    public static function assert($type): void
    {
        if(!array_key_exists($type, self::list())){
            BrandCalcException::throwWrongType();
        }
    }

    public static function check($type): bool
    {
        return array_key_exists($type, self::list());
    }

    public static function create(int $type): self
    {
        self::assert($type);

        $self = new self();
        $self->type = $type;

        return $self;
    }
}



