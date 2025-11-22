<?php

namespace App\ValueObjects;

use App\Enums\Projects\Systems\WarrantyStatus;
use Core\ValueObjects\AbstractValueObject;
use InvalidArgumentException;

class WarrantyStatusValue extends AbstractValueObject
{
    protected function validate(string $value): void
    {
        if (!WarrantyStatus::hasValue($value)) {
            throw new InvalidArgumentException(__('The value must be a valid Warranty status!'));
        }
    }
}
