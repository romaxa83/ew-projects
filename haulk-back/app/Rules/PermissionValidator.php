<?php

namespace App\Rules;

use App\Models\Users\User;
use App\Permissions\Permission;
use App\Services\Permissions\PermissionService;
use Illuminate\Contracts\Validation\Rule;

class PermissionValidator implements Rule
{

    private string $guard;

    public function __construct(string $guard = User::GUARD)
    {
        $this->guard = $guard;
    }

    public function passes($attribute, $value): bool
    {
        $permissions = app(PermissionService::class)
            ->getFlattenPermissions($this->guard)
            ->mapWithKeys(fn(Permission $permission) => [$permission->getKey() => $permission->getKey()]);

        return (bool)$permissions->get($value);
    }

    public function message(): string
    {
        return __('validation.permission_not_exists');
    }
}
