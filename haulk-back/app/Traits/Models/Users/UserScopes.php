<?php

namespace App\Traits\Models\Users;

use App\Models\Users\User;
use App\Traits\Models\IsJoinedTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

/**
 * Trait UserScopes
 *
 * @see User::scopeAuthHistorySortable()
 * @method static Builder|User authHistorySortable($direction)
 *
 * @see User::scopeSort()
 * @method static Builder|User sort(string $column, string $direction)
 *
 * @see User::scopeJoinRoles()
 * @method static Builder|User joinRoles()
 *
 * @see User::scopeWithoutSuperDrivers()
 * @method static Builder|User withoutSuperDrivers()
 *
 * @see User::scopeOnlyDispatchers()
 * @method static Builder|User onlyDispatchers()
 *
 * @see User::scopeActive()
 * @method static Builder|User active(boolean $status = true)
 *
 * @see User::scopeOnlyDrivers()
 * @method static Builder|User onlyDrivers()
 *
 * @see User::scopeBelongsToMe()
 * @method static Builder|User belongsToMe()
 *
 * @see User::scopeCanCreateOrders()
 * @method static Builder|User canCreateOrders()
 *
 * @see User::scopeOnlyBodyShopUsers()
 * @method static Builder|User onlyBodyShopUsers()
 *
 * @see User::scopeWithoutBSSuperAdmin()
 * @method static Builder|User withoutBSSuperAdmin()
 *
 * @package App\Traits\Models\Users
 */
trait UserScopes
{
    use IsJoinedTrait;

    public function scopeBelongsToMe(Builder $query): Builder
    {
        return $query->where('owner_id', Auth::id());
    }

    public function scopeExcludeMyself($query)
    {
        return $query->where('id', '!=', Auth::id());
    }

    public function scopeAuthHistorySortable(Builder $builder, $direction)
    {
        return $builder->leftJoin('auth_history', 'users.id', '=', 'auth_history.user_id')
            ->orderBy('auth_history.created_at', $direction)
            ->groupBy('users.id')
            ->select('users.*');
    }

    public function scopeJoinRoles(Builder $builder)
    {
        $pivot = config('permission.table_names.model_has_roles');
        $joined = config('permission.table_names.roles');

        return $builder->join(
            $pivot,
            function (JoinClause $clause) use ($pivot) {
                $clause->on($pivot . '.model_id', '=', $this->getTable() . '.id')
                    ->where($pivot . '.model_type', self::class);
            }
        )->join($joined, $joined . '.id', '=', $pivot . '.role_id');
    }

    /**
     * @param self|Builder $builder
     * @param string $order
     * @param string $direction
     * @return self|Builder
     */
    public function scopeSort(Builder $builder, string $order, string $direction = 'asc')
    {
        if ($order == 'last_login') {
            return $builder->authHistorySortable($direction);
        }

        if ($order === 'full_name') {
            return $builder->orderByRaw('concat(first_name, \' \', last_name) ' . $direction);
        }

        return $builder->orderBy($order, $direction);
    }

    /**
     * @param self|Builder $builder
     */
    public function scopeWithoutSuperDrivers(Builder $builder)
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where('name', '!=', User::SUPERDRIVER_ROLE);
            }
        );
    }

    public function scopeOnlyDispatchers(Builder $builder)
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where('name', '=', User::DISPATCHER_ROLE);
            }
        );
    }

    public function scopeOnlyDrivers(Builder $builder)
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->whereIn('name', User::DRIVER_ROLES);
            }
        );
    }

    public function scopeOnlySuperadmins(Builder $builder)
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where('name', '=', User::SUPERADMIN_ROLE);
            }
        );
    }

    public function scopeCanCreateOrders(Builder $builder)
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->whereIn('name', [
                    User::SUPERADMIN_ROLE,
                    User::ADMIN_ROLE,
                    User::DISPATCHER_ROLE,
                ]);
            }
        );
    }

    public function scopeActive(Builder $builder, string $status = User::STATUS_ACTIVE)
    {
        $builder->where('status', $status);
    }

    /**
     * @param self|Builder $builder
     */
    public function scopeOnlyBodyShopUsers(Builder $builder): void
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->whereIn('name', User::BS_ROLES);
            }
        );
    }

    /**
     * @param self|Builder $builder
     */
    public function scopeWithoutBSSuperAdmin(Builder $builder): void
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where('name', '!=', User::BSSUPERADMIN_ROLE);
            }
        );
    }
}
