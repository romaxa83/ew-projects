<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Volume
{
    protected float $value;
    protected int $convertValue = 10;
    protected bool $toDbConvert;
    protected bool $fromDbConvert;

    public function __construct(
        $value,
        bool $toDbConvert = false,
        bool $fromDbConvert = false
    )
    {
        $value = $this->filter($value);
        $this->validate($value);

        $this->toDbConvert = $toDbConvert;
        $this->fromDbConvert = $fromDbConvert;

        $this->value = $value;

        if($this->isConvertToDb()){
            $this->toDb($value);
        }

        if($this->isConvertFomDb()){
            $this->fromDb($value);
        }

    }

    public static function instanceToDbConvert($value): self
    {
        return new self($value, true);
    }

    public static function instanceFromDbConvert($value): self
    {
        return new self($value, false,true);
    }

    protected function filter($value)
    {
        return str_replace([' ',], '', $value);
    }

    protected function validate(string $value): void
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException('Value must be a number!');
        }
    }

    public function __toString()
    {
        return $this->value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getValueToDb(): int
    {
        return $this->value / $this->convertValue;
    }

    public function isConvertFomDb(): bool
    {
        return $this->fromDbConvert;
    }

    public function isConvertToDb(): bool
    {
        return $this->toDbConvert;
    }

    private function toDb(float $value): void
    {

        $this->value = $value * $this->convertValue;
    }

    private function fromDb(int $value): void
    {
        $this->value = $value / $this->convertValue;
    }
}
