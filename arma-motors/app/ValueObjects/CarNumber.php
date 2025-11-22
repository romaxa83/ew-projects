<?php

namespace App\ValueObjects;

class CarNumber extends AbstractValueObject
{
    protected function filter(string $value): string
    {
        $value = mb_strtoupper(str_replace([ ' '], '', $value));

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {}
}
