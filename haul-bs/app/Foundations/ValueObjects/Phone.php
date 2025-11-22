<?php

namespace App\Foundations\ValueObjects;

use InvalidArgumentException;

class Phone extends BaseValueObject
{
    protected function filter(string $value): string
    {
        $value = phone_clear($value);

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^\+?\d{9,20}$/', $value)) {
            throw new InvalidArgumentException(
                __('exceptions.value_object.value_must_be_phone', [
                    'value' => $value
                ])
            );
        }
    }
}

