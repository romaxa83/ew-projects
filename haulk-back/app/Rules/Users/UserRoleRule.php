<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class UserRoleRule implements Rule
{
    public const DISPATCHER = 'dispatcher';
    public const DRIVER = 'driver';

    private const ALLOWED_ROLES_NAME = [
        self::DISPATCHER => [
            User::SUPERADMIN_ROLE,
            User::ADMIN_ROLE,
            User::DISPATCHER_ROLE,
        ],
        self::DRIVER => [
            User::DRIVER_ROLE,
            User::OWNER_DRIVER_ROLE,
        ],
    ];

    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function passes($attribute, $value): bool
    {
        $user = User::find($value);
        return in_array($user->getRoleName(), self::ALLOWED_ROLES_NAME[$this->role]);
    }

    public function message(): string
    {
        return trans(Str::ucfirst($this->role) . ' not found.');
    }
}
