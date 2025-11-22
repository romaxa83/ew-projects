<?php

namespace Core\Traits\Auth;

use App\Contracts\Models\HasGuard;
use App\Enums\Permissions\GuardsEnum;
use App\Models\Admins\Admin;
use App\Models\BasicAuthenticatable;
use App\Models\Users\User;
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

    protected function user(string $guard = null): BasicAuthenticatable|Authenticatable|User|Admin
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

    protected function manager(): User
    {
        return $this->user(User::GUARD);
    }

    protected function isAdmin(): bool
    {
        return $this->user() instanceof Admin;
    }

    protected function guest(string $guard = null): bool
    {
        return $this->getAuthGuard($guard ?? $this->guard)
            ->guest();
    }

    protected function setAdminGuard(): void
    {
        $this->guard = GuardsEnum::ADMIN;
    }

    protected function setUserGuard(): void
    {
        $this->guard = GuardsEnum::USER;
    }

    protected function getAuthUser(): Authenticatable|Admin|User|null
    {
        $guards = GuardsEnum::getValues();

        foreach ($guards as $guard) {
            $user = Auth::guard($guard)
                ->user();

            if ($user instanceof HasGuard) {
                return $user;
            }
        }

        return null;
    }
}
