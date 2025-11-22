<?php

namespace Core\Rules;

use App\Models\Users\User;
use Core\Permissions\Permission;
use Core\Services\Permissions\PermissionService;
use Illuminate\Contracts\Validation\Rule;

class PermissionKeyValidator implements Rule
{
    public function __construct(protected string $guard = User::GUARD)
    {
    }

    public function passes($attribute, $value): bool
    {
        $permissions = app(PermissionService::class)
            ->getPermissionsList($this->guard)
            ->mapWithKeys(fn(Permission $permission) => [$permission->getKey() => $permission->getKey()]);

        return (bool)$permissions->contains($value);
    }

    public function message(): string
    {
        return __('validation.permission_not_exists');
    }
}
