<?php

namespace App\Rules;

use App\Models\Permissions\Role;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class RoleIdValidator implements Rule
{

    private string $guard;

    public function __construct(string $guard = User::GUARD)
    {
        $this->guard = $guard;
    }

    public function passes($attribute, $value): bool
    {
        return Role::query()
            ->where('id', $value)
            ->where('guard_name', $this->guard)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.role_id_is_not_exists_or_wrong_guard_scope');
    }
}