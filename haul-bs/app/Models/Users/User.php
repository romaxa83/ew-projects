<?php

namespace App\Models\Users;

use App\Enums\Users\UserStatus;
use App\Foundations\Casts\Contact\EmailCast;
use App\Foundations\Casts\Contact\PhoneCast;
use App\Foundations\Models\BaseAuthenticatableModel;
use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\UserProfileImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Foundations\Traits\Filters\Filterable;
use App\Foundations\Traits\Models\FullNameTrait;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\ModelFilters\Users\UserFilter;
use App\Models\Forms\Draft;
use App\Models\Orders\BS\Order;
use Carbon\Carbon;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property UserStatus status
 * @property string first_name
 * @property string last_name
 * @property string second_name
 * @property Email email
 * @property Phone|null phone
 * @property string|null phone_extension
 * @property array phones
 * @property string password
 * @property string remember_token
 * @property Carbon|null email_verified_at
 * @property int|null email_verified_code
 * @property int|null password_verified_code
 * @property string|null lang
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon|null deleted_at
 * @property int|null origin_id
 *
 * @see self::drafts()
 * @property Draft[]|HasMany drafts
 *
 * @see self::bsOrders()
 * @property Order[]|HasMany bsOrders
 *
 * @see User::scopeWithoutSuperAdmin()
 * @method static Builder|User withoutSuperAdmin()
 *
 * @method static UserFactory factory(...$parameters)
 */

class User extends BaseAuthenticatableModel implements HasMedia
{
    use Notifiable;
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use InteractsWithMedia;
    use FullNameTrait;

    protected $table = self::TABLE;
    public const TABLE = 'users';

    public const MEDIA_COLLECTION_NAME = 'photo';

    public const MORPH_NAME = 'user';
    public const PREFIX_DELETE_EMAIL = '-id@not-email.com';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_verified_code',
        'email_verified_code',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
        'status' => UserStatus::class,
        'phones' => 'array',
    ];

    public function getImageClass(): string
    {
        return UserProfileImage::class;
    }

    public function modelFilter(): string
    {
        return UserFilter::class;
    }

    public const ALLOWED_SORTING_FIELDS = [
        'email',
        'full_name',
        'status',
    ];

    public function isActive(): bool
    {
        return $this->status == UserStatus::ACTIVE;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at != null;
    }

    public function getName(): string
    {
        return $this->full_name;
    }

    public function scopeWithoutSuperAdmin(Builder $builder): void
    {
        $builder->whereHas(
            'roles',
            function (Builder $builder) {
                $builder->where('name', '!=', SuperAdminRole::NAME);
            }
        );
    }

    public function getFileBrowserPrefix(): string
    {
        return 'bs';
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }

    public function bsOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'mechanic_id');
    }

    public function hasRelatedOpenBSOrders(): bool
    {
        return $this->bsOrders()->open()->exists();
    }

    public function hasRelatedDeletedBSOrders(): bool
    {
        return $this->bsOrders()->onlyTrashed()->exists();
    }
}
