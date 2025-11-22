<?php

namespace App\ValueObjects;

use Core\ValueObjects\AbstractValueObject;
use InvalidArgumentException;

class Phone extends AbstractValueObject
{
    protected function filter(string $value): string
    {
        $value = str_replace(['+', '-', ' ', '.', '(', ')', '_'], '', $value);

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^\d{9,20}$/', $value)) {
            throw new InvalidArgumentException(__('The value must be a valid phone number!') . ' ' . $value);
        }
    }
}
