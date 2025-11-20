<?php

namespace App\Rules\Auth;

class AuthUniqueEmailRule extends BaseAuthUniqueFieldRule
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
