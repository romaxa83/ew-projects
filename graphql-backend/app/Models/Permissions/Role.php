<?php

namespace App\Models\Permissions;

use App\Filters\Permissions\RoleFilter;
use App\Models\BaseModel;
use App\Models\ListPermission;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\ModelMain;
use App\Traits\Permissions\DefaultListPermissionTrait;
use Database\Factories\Permissions\RoleFactory;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * @property int id
 * @property string guard_name
 * @property string name
 * @property bool|null for_owner
 * @property string created_at
 * @property string updated_at
 * @property Collection|Permission[] permissions
 *
 * @see Role::translates()
 * @property Collection|RoleTranslates[] translates
 *
 * @see Role::translate()
 * @property RoleTranslates translate
 *
 * @property-read string[] permissionList
 * @see Role::getPermissionListAttribute()
 *
 * @method static static|Builder query()
 * @method Builder|static select(...$attrs)
 *
 * @see Role::scopeForUsers()
 * @method Builder|static forUsers()
 *
 * @see Role::scopeDefaultForOwner()
 * @method Builder|static defaultForOwner()
 *
 * @method Builder|static whereName(string $name)
 * @method Builder|static whereGuardName(string $name)
 *
 * @method static RoleFactory factory()
 *
 * @mixin BaseModel
 */
class Role extends \Spatie\Permission\Models\Role implements ListPermission
{
    use ModelMain;
    use Filterable;
    use HasFactory;
    use DefaultListPermissionTrait;

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'created_at',
        'updated_at',
        'title',
        'for_owner',
    ];
    public const FOR_OWNER = true;
    public const TABLE = 'roles';
    protected $fillable = [
        'name',
        'guard_name',
        'for_owner',
    ];
    protected $casts = [
        'for_owner' => 'boolean',
    ];

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

    /**
     * @throws Throwable
     */
    public function setAsDefaultForOwner(): void
    {
        $this->checkForUsers();

        static::query()
            ->forUsers()
            ->update(['for_owner' => !self::FOR_OWNER]);

        $this
            ->setForOwner()
            ->save();
    }

    /**
     * @throws Throwable
     */
    protected function checkForUsers(): void
    {
        if (!$this->isForUsers()) {
            throw new Exception(__('exceptions.role_is_not_for_users'));
        }
    }

    protected function isForUsers(): bool
    {
        return $this->guard_name === User::GUARD;
    }

    protected function setForOwner(): self
    {
        $this->for_owner = self::FOR_OWNER;

        return $this;
    }

    public function isForOwner(): bool
    {
        return (bool)$this->for_owner;
    }

    public function scopeForUsers(Builder|self $q): void
    {
        $q->where('guard_name', User::GUARD);
    }

    public function scopeDefaultForOwner(Builder|self $q): void
    {
        $q->where('for_owner', self::FOR_OWNER);
    }
}
