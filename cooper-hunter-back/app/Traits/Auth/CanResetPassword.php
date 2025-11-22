<?php

namespace App\Traits\Auth;

use App\GraphQL\InputTypes\Auth\Password\ChangePasswordInput;
use App\Models\Users\User;
use App\Rules\PasswordRule;

trait CanResetPassword
{
    protected function passwordArg(string $argName = 'credentials'): array
    {
        return [
            $argName => ChangePasswordInput::type()
        ];
    }

    protected function passwordRule(string $ruleName = 'credentials'): array
    {
        return [
            $ruleName => ['nullable', 'array'],
            $ruleName.'.current_password' => ['current_password:'.$this->guard ?? User::GUARD],
            $ruleName.'.password' => ['confirmed', new PasswordRule()],
        ];
    }
}
