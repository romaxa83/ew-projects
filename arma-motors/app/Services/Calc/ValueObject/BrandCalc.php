<?php

namespace App\Services\Calc\ValueObject;

use App\Services\Calc\Exception\BrandCalcException;

final class BrandCalc
{
    const RENAULT    = 1;
    const MITSUBISHI = 2;
    const VOLVO      = 3;

    private int $type;

    private function __construct(){}

    public static function list()
    {
        return [
            self::RENAULT => 'renault',
            self::MITSUBISHI => 'mitsubishi',
            self::VOLVO => 'volvo',
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

    public function isVolvo(): bool
    {
        return $this->type === self::VOLVO;
    }

    public function isMitsubishi(): bool
    {
        return $this->type === self::MITSUBISHI;
    }

    public function isRenault(): bool
    {
        return $this->type === self::RENAULT;
    }
}


