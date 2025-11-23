<?php

namespace App;

use App\Helpers\DbConnections;
use App\Models\User\Role;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\{
    Builder,
    Collection
};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Auth;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\User
 *
 * @property int $id
 * @property int $active
 * @property string $name
 * @property string $email
 * @property array|null $division_ids
 * @property-read int|null $tokens_count
 * @property int $tmp_is_admin
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property array|null $miscs JSON
 * @property \Illuminate\Support\Carbon|null $created_at GTM
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereActive($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereMiscs($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTmpIsAdmin($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereDivisionIds($value)
 * @method static Builder|User userDetails()
 * @method static Builder|User selectedUsersOrActive($selected)
 * @property-read Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @method static UserFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;
    use HasFactory;

    public const ROLE_FOREMAN_ID = 2;

    public const TABLE = 'users';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'miscs' => 'json',
        'division_ids' => 'array',
    ];


    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($item) {
            self::updateManagersCache();
        });
        static::updated(function ($item) {
            self::updateManagersCache();
        });
        static::deleted(function ($item) {
            self::updateManagersCache();
        });
    }

    private static function updateManagersCache()
    {
//        Cache::put('nav_managers', self::all(['id', 'name'])->keyBy('id'), now()->addHours(6));
    }

    public function employee()
    {
        return $this->hasOne(Models\Employee::class, 'auth_user_id', 'id')
            ->orderBy('active', 'desc')
            ;
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'users_roles_2_users',
            'user_id',
            'role_id'
        );
    }

    public static function isAdmin(): bool
    {
        return self::inRole(1);
    }

    public static function isCanView(): bool
    {
        if(self::inRole(2)){
            if(!self::inRole(1)){
                return false;
            }
        }
        return true;
    }

    public function isPartner(): bool
    {
        if($this->roles->isNotEmpty()) {
            return $this
                ->roles
                ->pluck('title')
                ->contains(Role::PARTNER)
                ;
        }

        return false;
    }

    public function isForeman(): bool
    {
        if($this->roles->isNotEmpty()) {
            return $this
                ->roles
                ->pluck('title')
                ->contains(Role::FOREMAN)
                ;
        }

        return false;
    }

    public function isAccountant(): bool
    {
        if($this->roles->isNotEmpty()) {
            return $this
                ->roles
                ->pluck('title')
                ->contains(Role::ACCOUNTANT)
                ;
        }

        return false;
    }

    public static function inRole($role_id): bool
    {
        return self::whereId(Auth::id())->whereHas('roles', function ($q) use ($role_id) {
            $q->where('role_id', $role_id);
        })
            ->exists();
    }

    public function isRoutePatternAllowed($route, $method = "GET"): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        return self::whereId(Auth::id())->whereHas('roles.allowedRoutes', function (Builder $q) use ($route, $method) {
            $q->where('name', 'LIKE', $route."%")
                ->where('method', $method);
        })->exists();
    }

    public function scopeUserDetails($q)
    {
        return $q->with(['roles:id,title'])
            ->orderBy('name')
            ->select(['id', 'name', 'email', 'active', 'division_ids']);
    }

    public function scopeSelectedUsersOrActive($q, $selected)
    {
        return $q->when($selected, function ($q, $ids) {
            $ids = array_filter($ids, 'is_numeric');
            $q->whereIn('id', $ids);
        }, function ($q) {
            $q->whereActive(1);
        });
    }
}
