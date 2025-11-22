<?php

namespace App\Models\Admin;

use Eloquent;
use Carbon\Carbon;
use App\Traits\Scopes;
use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Models\Media\Image;
use App\Models\Order\Order;
use App\Types\Order\Status;
use App\ValueObjects\Phone;
use App\ValueObjects\Email;
use App\Models\Languageable;
use App\Traits\SetPasswordTrait;
use Laravel\Passport\HasApiTokens;
use App\Traits\Permissions\HasRoles;
use App\Models\BasicAuthenticatable;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\Department;
use App\Models\Localization\Language;
use App\Models\Catalogs\Service\Service;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\Admin\AdminFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int id
 * @property string name
 * @property string password
 * @property string lang
 * @property int|null dealership_id
 * @property int|null department_type
 * @property int|null service_id
 * @property Email email
 * @property null|Phone phone
 * @property int status
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property null|Carbon deleted_at
 *
 * @method static static|Builder whereEmail($email)
 * @method static AdminFactory factory(...$parameters)
 * @mixin Eloquent
 */
class Admin extends BasicAuthenticatable implements Languageable
{
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasApiTokens;
    use SetPasswordTrait;
    use SoftDeletes;
    use Scopes\SuperAdmin;

    public const STATUS_ACTIVE   = 1;
    public const STATUS_INACTIVE = 0;

    public const GUARD = 'graph_admin';

    public const TABLE = 'admins';

    public const IMAGE_AVATAR_TYPE = 'avatar'; // тип в images для аватарки

    public static $snakeAttributes = true;

    protected $table = self::TABLE;

    protected $appends = [
        'dealership'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'lang'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
    ];

    // Checkers
    public function isActive(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function isInActive(): bool
    {
        return $this->status == self::STATUS_INACTIVE;
    }

    public function isServiceDepartment(): bool
    {
        return $this->department_type == Department::TYPE_SERVICE;
    }

    public function isBodyDepartment(): bool
    {
        return $this->department_type == Department::TYPE_BODY;
    }

    public function isSalesDepartment(): bool
    {
        return $this->department_type == Department::TYPE_SALES;
    }

    public function isCreditDepartment(): bool
    {
        return $this->department_type == Department::TYPE_CREDIT;
    }

    public function hasDepartment(): bool
    {
        return $this->department_type !== null;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }
    public function getLangSlug(): string
    {
        return $this->lang;
    }

    // Relations
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'entity');
    }
    public function logins(): HasMany
    {
        return $this->hasMany(Login::class);
    }
    public function locale(): HasOne
    {
        return $this->hasOne(Language::class, 'slug', 'lang');
    }
    public function avatar(): MorphOne
    {
        return $this->morphOne(Image::class, 'entity')->where('type', self::IMAGE_AVATAR_TYPE);
    }
    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function orders(): HasMany
    {
        return $this->HasMany(Order::class);
    }

    public function ordersClose(): HasMany
    {
        return $this->orders()->where('status', Status::CLOSE);
    }

    public function ordersWithTrashed(): HasMany
    {
        return $this->HasMany(Order::class)->withTrashed();
    }

    public function ordersCloseWithTrashed(): HasMany
    {
        return $this->ordersWithTrashed()->where('status', Status::CLOSE);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    // получение данных по последнему логину
    public function scopeWithLastLoginAt(Builder $query)
    {
        $query->addSelect(['lastLoginAt' => Login::select('created_at')
            ->whereColumn('admin_id', 'admins.id')
            ->latest()
            ->take(1)
        ])
            ->withCasts(['lastLoginAt' => 'datetime']);
    }

    public function scopeNameSearch(Builder $query, string $search): Builder
    {
        return $query->where('admins.name','like', '%' . $search . '%');
    }

    public function scopeRoleSort(Builder $query, string $val): Builder
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select([
                    'admins.*',
//                    'roles.*',
//                    'roles_translations.*'
                ])
                ->join('model_has_roles', 'admins.id' , '=', 'model_has_roles.model_id')
                ->join('roles', 'roles.id' , '=', 'model_has_roles.role_id')
//                ->join('roles_translations', 'roles_translations.role_id' , '=', 'roles.id')
//                ->orderBy('roles_translations.name', $val);
                ->orderBy('roles.name', $val);
        }
    }

    public function scopeCountOrdersSort(Builder $query, string $val): Builder
    {
        if($this->checkGraphqlSort($val)){
            return $query->withCount('orders')
                ->orderBy('orders_count', $val);
        }
    }

    public function scopeCountOrdersCloseSort(Builder $query, string $val): Builder
    {
        if($this->checkGraphqlSort($val)){
            return $query->withCount('ordersClose')
                ->orderBy('orders_close_count', $val);
        }
    }
}
