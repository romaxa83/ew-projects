<?php

namespace App\Rules;

use App\ValueObjects\Phone;
use Throwable;

class MemberUniquePhoneRule extends BaseMemberUniqueFieldRule
{
    protected static function getFieldToCheck(): string
    {
        return 'phone';
    }

    protected static function gerValidationMessage(): string
    {
        return __('validation.unique_phone');
    }

    protected function serializeValue(string $value): string
    {
        try {
            return (string)(new Phone($value));
        } catch (Throwable) {
            return $value;
        }
    }
}
