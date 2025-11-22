<?php

namespace App\Rules\ExistsRules;

use App\Models\Permissions\Role;
use Illuminate\Contracts\Validation\Rule;

class UserRoleExistsRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return Role::query()
            ->forUsers()
            ->whereKey($value)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.exists');
    }
}
