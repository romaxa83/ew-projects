<?php

namespace WezomCms\Core\Models;

use Eloquent;
use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticated;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Notifications\ResetPassword;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\GetForSelectTrait;
use WezomCms\Core\Traits\Model\ImageAttachable;

/**
 * \WezomCms\Core\Models\Administrator
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $api_token
 * @property string|null $image
 * @property bool $active
 * @property bool $super_admin
 * @property bool $notify
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @method static Builder|Administrator filter($input = [], $filter = null)
 * @method static Builder|Administrator newModelQuery()
 * @method static Builder|Administrator newQuery()
 * @method static Builder|Administrator notSuperAdmin()
 * @method static Builder|Administrator paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Administrator permission($permissions)
 * @method static Builder|Administrator query()
 * @method static Builder|Administrator simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Administrator superAdmin()
 * @method static Builder|Administrator toNotifications($permissions)
 * @method static Builder|Administrator whereActive($value)
 * @method static Builder|Administrator whereApiToken($value)
 * @method static Builder|Administrator whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Administrator whereCreatedAt($value)
 * @method static Builder|Administrator whereEmail($value)
 * @method static Builder|Administrator whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Administrator whereId($value)
 * @method static Builder|Administrator whereImage($value)
 * @method static Builder|Administrator whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Administrator whereName($value)
 * @method static Builder|Administrator whereNotify($value)
 * @method static Builder|Administrator wherePassword($value)
 * @method static Builder|Administrator whereRememberToken($value)
 * @method static Builder|Administrator whereSuperAdmin($value)
 * @method static Builder|Administrator whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Administrator extends Authenticated implements HasLocalePreference
{
    use Filterable;
    use HasFactory;
    use Notifiable;
    use ImageAttachable;
    use GetForSelectTrait;
    use EloquentTentacle;

    public const TABLE = 'administrators';
    protected $table = self::TABLE;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = ['notify' => true];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'notify'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['active' => 'bool', 'super_admin' => 'bool', 'notify' => 'bool'];

    /**
     * Get the preferred locale of the entity.
     *
     * @return string|null
     */
    public function preferredLocale()
    {
        return config('cms.core.translations.admin.default');
    }

    public function isModerator(): bool
    {
        return $this->roles->where('name', Role::DEFAULT_MODERATOR)->isNotEmpty();
    }

    public function isProvider(): bool
    {
        return $this->roles->where('name', Role::DEFAULT_PROVIDER)->isNotEmpty();
    }

    public function onlyProvider(): bool
    {
        return $this->isProvider() && !$this->isModerator();
    }

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.core.administrator.images'];
    }

    public function scopeProviders(Builder $query): void
    {
        $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::DEFAULT_PROVIDER);
        });
    }

    /**
     * @param Builder|Administrator $query
     * @param array $permissions
     */
    public function scopeToNotifications(Builder $query, ...$permissions)
    {
        $query->where('notify', true);

        if ($permissions) {
            $query->permission(...$permissions);
        } else {
            $query->superAdmin();
        }
    }

    /**
     * @param Builder $query
     */
    public function scopeSuperAdmin(Builder $query)
    {
        $query->where('super_admin', true);
    }

    /**
     * @param Builder $query
     */
    public function scopeNotSuperAdmin(Builder $query)
    {
        $query->where('super_admin', false);
    }

    /**
     * @param Builder|Administrator $query
     * @param mixed ...$permissions
     */
    public function scopePermission(Builder $query, ...$permissions)
    {
        $permissions = is_array(array_get($permissions, 0)) ? $permissions[0] : $permissions;

        $query->where(
            function (Builder $query) use ($permissions) {
                /** @var Administrator $query */
                $query->superAdmin()
                    ->orWhereHas(
                        'roles',
                        function (Builder $query) use ($permissions) {
                            $query->where(
                                function (Builder $query) use ($permissions) {
                                    foreach ($permissions as $permission) {
                                        $query->orWhereJsonContains('permissions', $permission);
                                    }
                                }
                            );
                        }
                    );
            }
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_administrators', 'administrator_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'provider_id', 'id');
    }

    // for provider

    /**
     * @param $permissions
     * @return bool
     */
    public function hasAccess($permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : func_get_args();

        foreach ($this->roles as $role) {
            if ($role->hasAccess($permissions)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->super_admin;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
