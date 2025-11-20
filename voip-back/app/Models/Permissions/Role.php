<?php

namespace App\Models\Permissions;

use App\Filters\Permissions\RoleFilter;
use App\Models\BaseModel;
use App\Models\ListPermission;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveTrait;
use App\Traits\Model\HasTranslations;
use App\Traits\Permissions\DefaultListPermissionTrait;
use Database\Factories\Permissions\RoleFactory;
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
 * @see Role::translations()
 * @property Collection|RoleTranslation[] translations
 *
 * @see Role::translation()
 * @property RoleTranslation translation
 *
 * @property-read string[] permissionList
 * @see Role::getPermissionListAttribute()
 *
 * @method static static|Builder query()
 * @method Builder|static select(...$attrs)
 *
 * @see Role::scopeWithoutSuperAdmin()
 * @method Builder|static withoutSuperAdmin()
 *
 * @method Builder|static whereName(string $name)
 * @method Builder|static whereGuardName(string $name)
 *
 * @method static RoleFactory factory()
 */
class Role extends \Spatie\Permission\Models\Role implements ListPermission
{
    use Filterable;
    use HasFactory;
    use HasTranslations;
    use DefaultListPermissionTrait;
    use ActiveTrait;

    protected $table = self::TABLE;
    public const TABLE = 'roles';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'created_at',
        'updated_at',
        'title',
    ];

    protected $fillable = [
        'name',
        'guard_name',
    ];
    protected $casts = [];

    public function modelFilter(): string
    {
        return RoleFilter::class;
    }

    public function getPermissionListAttribute(): array
    {
        return $this->permissions
            ->pluck('name')
            ->toArray();
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === static::superAdminName();
    }

    public function isEmployee(): bool
    {
        return $this->name === static::employeeName();
    }

    public static function superAdminName(): string
    {
        return config('permission.roles.super_admin');
    }

    public static function employeeName(): string
    {
        return config('permission.roles.employee');
    }

    public function scopeForUsers(Builder|self $q): void
    {
        $q->where('guard_name', User::GUARD);
    }

    public function scopeWithoutSuperAdmin(Builder|self $q): void
    {
        $q->where('name', '!=', static::superAdminName());
    }
}
