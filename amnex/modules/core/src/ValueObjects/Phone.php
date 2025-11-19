<?php

namespace Wezom\Core\ValueObjects;

use InvalidArgumentException;

class Phone extends AbstractValueObject
{
    public static function from(string $value): Phone
    {
        return new Phone($value);
    }

    protected function filter(string $value): string
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^\d{9,20}$/', $value)) {
            throw new InvalidArgumentException(__('The value must be a valid phone number!') . ' ' . $value);
        }
    }
}
