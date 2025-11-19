<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Permission;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Database\Factories\Permission\RoleFactory;
use Wezom\Core\Dto\FilteringDto;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\GraphQL\Types\AbilitiesList;
use Wezom\Core\ModelFilters\RoleFilter;
use Wezom\Core\Traits\Model\ActiveScopeTrait;
use Wezom\Core\Traits\Model\Filterable;
use Wezom\Core\Traits\Model\Permittable;

/**
 * \Wezom\Core\Models\Permission\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $title
 * @property RoleEnum|null $system_type
 * @property string|null $note
 * @property bool $active
 * @property-read AbilitiesList $abilities
 * @property-read array $permission_list
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Model> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Role active(array|bool $value = true)
 * @method static RoleFactory factory($count = null, $state = [])
 * @method static Builder<static>|Role filter(array $input = [], $filter = null)
 * @method static Builder<static>|Role filterWithOrder(FilteringDto $filtering)
 * @method static Builder<static>|Role forAdmins()
 * @method static Builder<static>|Role newModelQuery()
 * @method static Builder<static>|Role newQuery()
 * @method static Builder<static>|Role paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|Role permission($permissions, $without = false)
 * @method static Builder<static>|Role query()
 * @method static Builder<static>|Role simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder<static>|Role superAdmin(bool $bool = true)
 * @method static Builder<static>|Role whereActive($value)
 * @method static Builder<static>|Role whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|Role whereCreatedAt($value)
 * @method static Builder<static>|Role whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder<static>|Role whereGuardName($value)
 * @method static Builder<static>|Role whereId($value)
 * @method static Builder<static>|Role whereLike($column, $value, $boolean = 'and')
 * @method static Builder<static>|Role whereName($value)
 * @method static Builder<static>|Role whereNote($value)
 * @method static Builder<static>|Role whereSystemType($value)
 * @method static Builder<static>|Role whereTitle($value)
 * @method static Builder<static>|Role whereType(RoleEnum|array|string $type)
 * @method static Builder<static>|Role whereUpdatedAt($value)
 * @method static Builder<static>|Role withoutPermission($permissions)
 * @mixin Eloquent
 */
class Role extends \Spatie\Permission\Models\Role
{
    use ActiveScopeTrait;
    use Filterable;
    use HasFactory;
    use Permittable;

    protected $fillable = [
        'name',
        'guard_name',
    ];
    protected $casts = [
        'system_type' => RoleEnum::class,
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

    public function getNameKey(): ?RoleEnum
    {
        return $this->system_type;
    }

    public function scopeForAdmins(Builder|self $q): void
    {
        $q->where('guard_name', Admin::GUARD);
    }

    public function scopeWhereType(Builder|self $q, string|RoleEnum|array $type): void
    {
        $q->whereIn('system_type', !is_array($type) ? [$type] : $type);
    }

    public function scopeSuperAdmin(Builder|self $builder, bool $bool = true): Builder
    {
        if ($bool) {
            return $builder->forAdmins()->where('system_type', RoleEnum::SUPER_ADMIN);
        }

        return $builder->where(
            fn (Builder $query) => $query->whereNull('system_type')
                ->orWhereNot('system_type', RoleEnum::SUPER_ADMIN)
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->system_type->isSuperAdmin();
    }
}
