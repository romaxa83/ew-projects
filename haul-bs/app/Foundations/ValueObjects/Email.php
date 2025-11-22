<?php

namespace App\Foundations\ValueObjects;

use InvalidArgumentException;

class Email extends BaseValueObject
{
    protected function validate(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                __('exceptions.value_object.value_must_be_email', [
                    'value' => $value
                ])
            );
        }
    }
}
