<?php

namespace App\Models\Permissions;

use App\Filters\Permissions\RoleFilter;
use App\Models\BaseModel;
use App\Models\Dealers\Dealer;
use App\Models\ListPermission;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Permissions\DefaultListPermissionTrait;
use Database\Factories\Permissions\RoleFactory;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * @property int id
 * @property string name
 * @property string guard_name
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
 * @see Role::scopeForUsers()
 * @method Builder|static forUsers()
 *
 * @see Role::scopeForTechnicians()
 * @method Builder|static forTechnicians()
 *
 * @see Role::scopeForDealers()
 * @method Builder|static forDealers()
 *
 * @method Builder|static whereName(string $name)
 * @method Builder|static whereGuardName(string $name)
 *
 * @method static RoleFactory factory(...$parameters)
 *
 * @mixin BaseModel
 */
class Role extends \Spatie\Permission\Models\Role implements ListPermission
{
    use HasTranslations;
    use Filterable;
    use HasFactory;
    use DefaultListPermissionTrait;

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'created_at',
        'updated_at',
        'title',
    ];

    public const TABLE = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
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

    public function scopeForUsers(Builder|self $q): void
    {
        $q->where('guard_name', User::GUARD);
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

    public function scopeForTechnicians(Builder|self $q): void
    {
        $q->where('guard_name', Technician::GUARD);
    }

    public function scopeForDealers(Builder|self $q): void
    {
        $q->where('guard_name', Dealer::GUARD);
    }
}
