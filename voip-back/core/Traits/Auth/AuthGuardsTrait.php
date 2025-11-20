<?php

namespace Core\Traits\Auth;

use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\Employees\Employee;
use App\Models\Users\User;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Services\Permissions\PermissionTreeStorage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

trait AuthGuardsTrait
{
    protected string $guard = User::GUARD;
    protected string $authMessage = AuthorizationMessageEnum::UNAUTHORIZED;

    public function can(string|array $permission, $arguments = []): bool
    {
        if (!isProd() && config('grants.permissions_disable')) {
            return true;
        }

        if (!$this->authCheck()) {
            return false;
        }

        $permissions = $this->normalizePermissions($permission);

        if (!$this->user()->canAny($permissions, $arguments)) {
            $this->authMessage = AuthorizationMessageEnum::NO_PERMISSION;

            return false;
        }

        return true;
    }

    protected function authCheck(array|string $guard = null): bool
    {
        if (is_array($guard)) {
            foreach ($guard as $g) {
                if ($this->getAuthGuard($g)->check()) {
                    return true;
                }
            }
        }

        return $this->getAuthGuard(is_string($guard) ? $guard : $this->guard)->check();
    }

    protected function getAuthGuard(string $guard = null): Guard|StatefulGuard
    {
        return Auth::guard($guard ?? $this->guard);
    }

    protected function setAuthGuard(): void
    {
        foreach (config('auth.auth_guards') as $guard) {
            if ($this->authCheck($guard)) {
                $this->guard = $guard;

                return;
            }
        }
    }

    protected function normalizePermissions(array|string $permission): array
    {
        $permissions = $this->normalizePermissionArray($permission);

        $permissions = array_merge(
            app(PermissionTreeStorage::class)->getAllMainRelated($permissions),
            $permissions,
        );

        return array_unique($permissions);
    }

    protected function normalizePermissionArray(array|string $permission): array
    {
        if (is_array($permission)) {
            return $permission;
        }

        return explode('|', $permission);
    }

    protected function user(string $guard = null): BaseAuthenticatable|Authenticatable|Employee|Admin|null
    {
        return $this->getAuthGuard($guard ?? $this->guard)->user();
    }

    public function getAuthorizationMessage(): string
    {
        return $this->authMessage;
    }

    public function authId(string $guard = null): ?int
    {
        return $this->getAuthGuard($guard ?? $this->guard)->id();
    }

    protected function isAdmin(): bool
    {
        return $this->user() instanceof Admin;
    }

    protected function isEmployee(): bool
    {
        return $this->user() instanceof Employee;
    }

    protected function guest(string $guard = null): bool
    {
        return $this->getAuthGuard($guard ?? $this->guard)->guest();
    }

    protected function setAdminGuard(): void
    {
        $this->guard = Admin::GUARD;
    }

    protected function setEmployeeGuard(): void
    {
        $this->guard = Employee::GUARD;
    }

    protected function returnEmptyIfGuest(array|Closure $rules): array
    {
        if ($this->guest()) {
            return [];
        }

        if ($rules instanceof Closure) {
            return $rules();
        }

        return $rules;
    }
}
