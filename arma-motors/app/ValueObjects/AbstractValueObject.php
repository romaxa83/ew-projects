<?php

namespace App\ValueObjects;

use TypeError;

abstract class AbstractValueObject
{
    protected string $value;

    public function __construct(string $value, $validate = true)
    {
        $value = $this->filter($value);

        if($validate){
            $this->validate($value);
        }

        $this->value = $value;
    }

    protected function filter(string $value): string
    {
        return $value;
    }

    /**
     * @param string $value
     * @throw InvalidArgumentException
     */
    abstract protected function validate(string $value): void;

    public function __toString(): string
    {
        return $this->value;
    }

    public function compare($object): bool
    {
        if (!$object instanceof static) {
            throw new TypeError('Object must be an instance of class: ' . static::class);
        }

        return $this->value === $object->value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
