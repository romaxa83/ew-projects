<?php

declare(strict_types=1);

namespace Wezom\Admins\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Wezom\Admins\Database\Factories\AdminFactory;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\ModelFilters\AdminFilter;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\GraphQL\Types\AbilitiesList;
use Wezom\Core\Models\Auth\BaseAuthenticatable;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Traits\Model\Authenticatable;
use Wezom\Core\Traits\Model\Filterable;
use Wezom\Core\Traits\Model\Permittable;

/**
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $password
 * @property string|null $phone
 * @property bool $verified
 * @property string|null $verification_code
 * @property bool $active
 * @property string|null $remember_token
 * @property string|null $email_verification_code
 * @property string|null $new_email_for_verification
 * @property string|null $new_email_verification_code
 * @property Carbon|null $new_email_verification_code_at
 * @property AdminStatusEnum|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AbilitiesList $abilities
 * @property-read bool $invite_accepted
 * @property-read Role|null $role
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static Builder|Admin active(bool $value = true)
 * @method static AdminFactory factory($count = null, $state = [])
 * @method static Builder|Admin filter(array $input = [], $filter = null)
 * @method static Builder|Admin forNotifications(Ability|array|string $permissions = [])
 * @method static AdminFilter newFilter(string $filter = null, array $input = [])
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Admin permission($permissions, $without = false)
 * @method static Builder|Admin query()
 * @method static Builder|Admin role($roles, $guard = null, $without = false)
 * @method static Builder|Admin simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Admin superAdmin()
 * @method static Builder|Admin whereActive($value)
 * @method static Builder|Admin whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereEmail($value)
 * @method static Builder|Admin whereEmailVerificationCode($value)
 * @method static Builder|Admin whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Admin whereFirstName($value)
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereLastName($value)
 * @method static Builder|Admin whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Admin whereNewEmailForVerification($value)
 * @method static Builder|Admin whereNewEmailVerificationCode($value)
 * @method static Builder|Admin whereNewEmailVerificationCodeAt($value)
 * @method static Builder|Admin wherePassword($value)
 * @method static Builder|Admin wherePhone($value)
 * @method static Builder|Admin whereRememberToken($value)
 * @method static Builder|Admin whereRoles(RoleEnum|array|string $roles, ?string $guard = null)
 * @method static Builder|Admin whereStatus($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 * @method static Builder|Admin whereVerificationCode($value)
 * @method static Builder|Admin whereVerified($value)
 * @method static Builder|Admin withoutPermission($permissions)
 * @method static Builder|Admin withoutRole($roles, $guard = null)
 *
 * @mixin Eloquent
 */
class Admin extends BaseAuthenticatable
{
    use Authenticatable;
    use Filterable;
    use HasFactory;
    use Permittable;

    public const GUARD = 'graph_admin';
    public const MIN_LENGTH_PASSWORD = 8;
    public const MAX_LENGTH_PASSWORD = 250;

    protected $fillable = [
        'email_verification_code',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];
    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'active' => 'boolean',
        'status' => AdminStatusEnum::class,
        'new_email_verification_code_at' => 'datetime',
    ];

    protected function checkAbilitiesPolicy(): bool
    {
        return false;
    }

    public function scopeActive(Builder|self $builder, bool $value = true): void
    {
        $builder->whereActive(true)->whereStatus(AdminStatusEnum::ACTIVE);
    }

    public function scopeWhereEmail(Builder|self $builder, string $email): void
    {
        $builder->where($this->getTable() . '.email', $email);
    }

    /**
     * @param  array<string>|array<Ability>|string|Ability  $permissions
     */
    public function scopeForNotifications(Builder|self $builder, array|string|Ability $permissions = []): Builder
    {
        $permissions = array_wrap($permissions);
        $permissions = array_map(
            static fn (string|Ability $p) => $p instanceof Ability ? $p->build() : $p,
            $permissions
        );

        return $builder->active()
            ->whereHas(
                'roles',
                fn (Builder|Role $query) => $query->superAdmin()
                    ->orWhereHas('permissions', fn ($q) => $q->whereIn('name', $permissions))
            );
    }

    public function getName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isEmailVerified(): bool
    {
        return !$this->email_verification_code;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getInviteAcceptedAttribute(): bool
    {
        return !$this->email_verification_code;
    }

    public function toggleActive(): static
    {
        $this->active = !$this->active;

        $this->toggleStatus();

        return $this;
    }

    public function toggleStatus(): static
    {
        $this->status = match (true) {
            !$this->active => AdminStatusEnum::INACTIVE,
            $this->email_verification_code !== null => AdminStatusEnum::PENDING,
            default => AdminStatusEnum::ACTIVE
        };

        return $this;
    }

    public function scopeSuperAdmin(Builder|self $query): void
    {
        $query->whereHas(
            'roles',
            fn (Builder|Role $builder) => $builder->superAdmin()
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles->contains('system_type', RoleEnum::SUPER_ADMIN);
    }
}
