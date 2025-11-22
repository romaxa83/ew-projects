<?php

namespace App\ValueObjects;

use Core\ValueObjects\AbstractValueObject;
use InvalidArgumentException;

class Phone extends AbstractValueObject
{
    protected function filter(string $value): string
    {
        $value = str_replace(['+', '-', ' ', '.', '(', ')'], '', $value);

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^\d{3,20}$/', $value)) {
            throw new InvalidArgumentException(__('exceptions.value_must_be_phone') . ' ' . $value);
        }
    }
}
