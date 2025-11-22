<?php

namespace App\Foundations\Modules\Permission\Models;

use App\Foundations\Modules\Permission\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int id
 * @property string guard_name
 * @property string name
 * @property string created_at
 * @property string updated_at
 * @property Collection|Permission[] permissions
 *
 * @method static Builder|static superAdmin()
 * @method static Builder|static mechanic()
 * @method static Builder|static salesManager()
 * @method static Builder|static admin()
 *
 */
class Role extends \Spatie\Permission\Models\Role
{
    protected $table = self::TABLE;
    public const TABLE = 'roles';
    public const GUARD_USER = 'api_user_guard';
    public const GUARD_ADMIN = 'api_admin_guard';

    public static function defaultRoles(): array
    {
        return [
            Roles\SuperAdminRole::class,
            Roles\AdminRole::class,
            Roles\MechanicRole::class,
            Roles\SalesManagerRole::class,
        ];
    }

    public function isRole(string $roleName): bool
    {
        return $this->name === $roleName;
    }

    public function isAdmin(): bool
    {
        return $this->name === Roles\AdminRole::NAME && $this->guard_name === Roles\AdminRole::GUARD;
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === Roles\SuperAdminRole::NAME && $this->guard_name === Roles\SuperAdminRole::GUARD;
    }

    public function isMechanic(): bool
    {
        return $this->name === Roles\MechanicRole::NAME && $this->guard_name === Roles\MechanicRole::GUARD;
    }

    public function isSalesManager(): bool
    {
        return $this->name === Roles\SalesManagerRole::NAME && $this->guard_name === Roles\SalesManagerRole::GUARD;
    }

    public function scopeSuperAdmin($query)
    {
        return $query
            ->where('name', Roles\SuperAdminRole::NAME)
            ->where('guard_name', Roles\SuperAdminRole::GUARD)
            ;
    }

    public function scopeMechanic($query)
    {
        return $query
            ->where('name', Roles\MechanicRole::NAME)
            ->where('guard_name', Roles\MechanicRole::GUARD)
            ;
    }

    public function scopeSalesManager($query)
    {
        return $query
            ->where('name', Roles\SalesManagerRole::NAME)
            ->where('guard_name', Roles\SalesManagerRole::GUARD)
            ;
    }

    public function scopeAdmin($query)
    {
        return $query
            ->where('name', Roles\AdminRole::NAME)
            ->where('guard_name', Roles\AdminRole::GUARD)
            ;
    }
}

