<?php

namespace WezomCms\Core\Models;

use Greabock\Tentacles\EloquentTentacle;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticated;
use Illuminate\Notifications\Notifiable;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Core\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator filter($input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator notSuperAdmin()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator superAdmin()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator toNotifications($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereSuperAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Administrator whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Administrator extends Authenticated implements HasLocalePreference
{
    use Filterable;
    use Notifiable;
    use ImageAttachable;
    use GetForSelectTrait;
    use EloquentTentacle;

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

    /**
     * @return array
     */
    public function imageSettings(): array
    {
        return ['image' => 'cms.core.administrator.images'];
    }

    /**
     * @param  Builder|Administrator  $query
     * @param  array  $permissions
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
     * @param  Builder  $query
     */
    public function scopeSuperAdmin(Builder $query)
    {
        $query->where('super_admin', true);
    }

    /**
     * @param  Builder  $query
     */
    public function scopeNotSuperAdmin(Builder $query)
    {
        $query->where('super_admin', false);
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return (bool) $this->super_admin;
    }

    /**
     * @param  Builder|Administrator  $query
     * @param  mixed  ...$permissions
     */
    public function scopePermission(Builder $query, ...$permissions)
    {
        $permissions = is_array(array_get($permissions, 0)) ? $permissions[0] : $permissions;

        $query->where(function (Builder $query) use ($permissions) {
            /** @var Administrator $query */
            $query->superAdmin()
                ->orWhereHas('roles', function (Builder $query) use ($permissions) {
                    $query->where(function (Builder $query) use ($permissions) {
                        foreach ($permissions as $permission) {
                            $query->orWhereJsonContains('permissions', $permission);
                        }
                    });
                });
        });
    }

    /**
     * @return mixed|Role[]
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_administrators', 'administrator_id');
    }

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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
