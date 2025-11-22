<?php

namespace App\Rules;

class MemberUniqueEmailRule extends BaseMemberUniqueFieldRule
{
    protected static function getFieldToCheck(): string
    {
        return 'email';
    }

    protected static function gerValidationMessage(): string
    {
        return __('validation.unique_email');
    }
}
