<?php

namespace Core\Traits\Auth;

use App\Contracts\Members\Member;
use App\Contracts\Roles\HasGuardUser;
use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\Dealers\Dealer;
use App\Models\OneC\Moderator;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Closure;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Exceptions\TranslatedException;
use Core\Services\Permissions\PermissionTreeStorage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

trait AuthGuardsTrait
{
    protected string $guard = User::GUARD;
    protected string $authMessage = AuthorizationMessageEnum::UNAUTHORIZED;

    public function resetAuthGuard(): void
    {
        $this->guard = User::GUARD;
        $this->authMessage = AuthorizationMessageEnum::UNAUTHORIZED;
    }

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

    protected function user(string $guard = null): BaseAuthenticatable|Authenticatable|Admin|Member|User|Technician|Dealer|null
    {
        return $this->getAuthGuard($guard ?? $this->guard)
            ->user();
    }

    public function getAuthorizationMessage(): string
    {
        return $this->authMessage;
    }

    public function authId(string $guard = null): ?int
    {
        return $this->getAuthGuard($guard ?? $this->guard)->id();
    }

    protected function setMemberGuard(): void
    {
        foreach (config('auth.member_guards') as $guard) {
            if ($this->authCheck($guard)) {
                $this->guard = $guard;

                return;
            }
        }
    }

    protected function isAdmin(): bool
    {
        return $this->authCheck(Admin::GUARD);
    }

    protected function isSuperAdmin(): bool
    {
        return $this->user(Admin::GUARD) && $this->user(Admin::GUARD)->isSuperAdmin();
    }

    protected function isApiModerator(): bool
    {
        return $this->authCheck(Moderator::GUARD);
    }

    protected function setAdminGuard(): void
    {
        $this->guard = Admin::GUARD;
    }

    protected function setTechnicianGuard(): void
    {
        $this->guard = Technician::GUARD;
    }

    protected function setUserGuard(): void
    {
        $this->guard = User::GUARD;
    }

    protected function setDealerGuard(): void
    {
        $this->guard = Dealer::GUARD;
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

    protected function guest(string $guard = null): bool
    {
        return $this->getAuthGuard($guard ?? $this->guard)->guest();
    }

    protected function getAuthUser(): ?HasGuardUser
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            $user = Auth::guard($guard)
                ->user();

            if ($user instanceof HasGuardUser) {
                return $user;
            }
        }

        return null;
    }
}
