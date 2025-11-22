<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Phone extends AbstractValueObject
{
    protected function filter(string $value): string
    {
        $value = str_replace(['+', '-', ' ', '(', ')'], '', $value);

        return parent::filter($value);
    }

    protected function validate(string $value): void
    {
        if (!preg_match('/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/', $value)) {
            throw new InvalidArgumentException(__('validation.not validate phone'));
        }
    }

    public function formatAA(): string
    {
        return '+' . $this->value;
    }
}
