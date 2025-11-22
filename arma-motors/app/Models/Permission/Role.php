<?php

namespace App\Models\Permission;

use App\Traits\HasFactory;
use App\Traits\Scopes\SuperAdmin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use phpseclib3\Crypt\Hash;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 * @property string created_at
 * @property string updated_at
 *
 * @property Collection|Permission[] permissions
 *
 * @see Role::getPermissionListAttribute()
 * @property-read string[] permissionList
 *
 * @see Role::scopeNotSuperAdmin()
 * @method static static|Builder notSuperAdmin()
 *
 * @method static static|Builder query()
 */
class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;
    use SuperAdmin;

    public function getPermissionListAttribute(): array
    {
        return $this->permissions
            ->pluck('name')
            ->toArray();
    }

    public function scopeNotSuperAdmin(Builder $query)
    {
        return $query->where('name', '!=', config('permission.roles.super_admin'));
    }

    // relations
    public function translations(): HasMany
    {
        return $this->hasMany(RoleTranslation::class);
    }

    public function current(): HasOne
    {
        return $this->hasOne(RoleTranslation::class)->where('lang', \App::getLocale());
    }
}

