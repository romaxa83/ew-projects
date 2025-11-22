<?php

namespace App\Models\Users;

use App\Collections\Models\Orders\MediaCollection;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Users\UserFilter;
use App\Models\BaseAuthenticatable;
use App\Models\BodyShop\Orders\Order;
use App\Models\DiffableInterface;
use App\Models\Files\HasMedia;
use App\Models\Files\Media;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Files\UserProfileImage;
use App\Models\Forms\Draft;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Saas\Company\Company;
use App\Models\Tags\HasTags;
use App\Models\Tags\HasTagsTrait;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\TrailerDriverHistory;
use App\Models\Vehicles\TrailerOwnerHistory;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\TruckDriverHistory;
use App\Models\Vehicles\TruckOwnerHistory;
use App\Notifications\MailResetPasswordToken;
use App\Scopes\CompanyScope;
use App\Traits\Diffable;
use App\Traits\HelperTrait;
use App\Traits\Models\Users\UserRelations;
use App\Traits\Models\Users\UserScopes;
use App\Traits\SetCompanyId;
use Database\Factories\Users\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\Users\User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $second_name
 * @property string $email
 * @property string|null $phone
 * @property array|null $phones
 * @property string $status
 * @property string|null $password
 * @property string|null $fcm_token
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $owner_id
 * @property string|null $language
 * @property string|null $phone_2
 * @property string|null $phone_3
 * @property string|null $phone_extension
 * @property string|null $phone_extension_2
 * @property string|null $phone_extension_3
 * @property-read Collection|Draft[] $drafts
 * @property-read int|null $drafts_count
 * @property-read Collection|Client[] $clients
 * @property-read int|null $clients_count
 * @property-read DriverInfo $driverInfo
 * @property-read Collection|Media[] $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read User $owner
 * @property-read Company $carrier
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Collection|Token[] $tokens
 * @property-read int|null $tokens_count
 * @property bool can_check_orders
 * @property int|null broker_id
 * @property int|null carrier_id
 * @property-read Collection|DriverLicense[] $driverLicenses
 *
 * @see User::getFullNameAttribute()
 * @property-read string $full_name
 *
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attrs)
 * @method static Builder|static filter($input = [], $filter = null)
 * @method static bool|null forceDelete()
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static onlyTrashed()
 * @method static Builder|static paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static permission($permissions)
 * @method static Builder|static query()
 * @method static bool|null restore()
 * @method static Builder|static role($roles, $guard = null)
 * @method static Builder|static simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereDeletedAt($value)
 * @method static Builder|static whereEmail($value)
 * @method static Builder|static whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|static whereFirstName($value)
 * @method static Builder|static whereFullName($value)
 * @method static Builder|static whereId($value)
 * @method static Builder|static whereLanguage($value)
 * @method static Builder|static whereLastName($value)
 * @method static Builder|static whereLike($column, $value, $boolean = 'and')
 * @method static Builder|static whereOwnerId($value)
 * @method static Builder|static wherePassword($value)
 * @method static Builder|static wherePhone($value)
 * @method static Builder|static whereRememberToken($value)
 * @method static Builder|static whereSecondName($value)
 * @method static Builder|static whereStatus($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @method Builder|static withTrashed()
 * @method Builder|static withoutTrashed()
 * @method static Builder|static wherePhone2($value)
 * @method static Builder|static wherePhone3($value)
 * @method static Builder|static wherePhoneExtension($value)
 * @method static Builder|static wherePhoneExtension2($value)
 * @method static Builder|static wherePhoneExtension3($value)
 *
 * @see static::truck()
 * @property  Truck|null truck
 *
 * @see static::trailer()
 * @property  Trailer|null trailer
 *
 * @see static::ownerTrucks()
 * @property  Collection|Truck[] ownerTrucks
 *
 * @see static::fuelCards()
 * @property  Collection|FuelCard[] fuelCards
 *
 * @see static::ownerTrailers()
 * @property  Collection|Trailer[] ownerTrailers
 *
 * @see static::bsOrders()
 * @property  Order[] bsOrders
 *
 * @see static::comments()
 * @property  UserComment[] comments
 *
 * @see static::driverTrucksHistory()
 * @property  Collection|TruckDriverHistory[] driverTrucksHistory
 *
 * @see static::driverTrailersHistory()
 * @property  Collection|TrailerDriverHistory[] driverTrailersHistory
 *
 * @see static::ownerTrucksHistory()
 * @property  Collection|TruckOwnerHistory[] ownerTrucksHistory
 *
 * @see static::ownerTrailersHistory()
 * @property  Collection|TrailerOwnerHistory[] ownerTrailersHistory
 *
 * @mixin Eloquent
 *
 * @method static UserFactory factory(...$parameters)
 */
class User extends BaseAuthenticatable implements HasMedia, HasTags, DiffableInterface
{
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use HelperTrait;
    use HasMediaTrait;
    use UserScopes;
    use UserRelations;
    use SetCompanyId;
    use HasTagsTrait;
    use HasFactory;
    use Diffable;

    public const TABLE_NAME = 'users';

    public const DISPATCHER_ROLE = 'Dispatcher';

    public const DRIVER_ROLE = 'Driver';

    public const ADMIN_ROLE = 'Admin';

    public const ACCOUNTANT_ROLE = 'Accountant';

    public const SUPERDRIVER_ROLE = 'Superdriver';

    public const SUPERADMIN_ROLE = 'Superadmin';

    public const OWNER_ROLE = 'Owner';

    public const OWNER_DRIVER_ROLE = 'Owner-driver';

    public const BSADMIN_ROLE = 'BodyShopAdmin';

    public const BSSUPERADMIN_ROLE = 'BodyShopSuperAdmin';

    public const BSMECHANIC_ROLE = 'BodyShopMechanic';

    public const BS_ROLES = [
        self::BSADMIN_ROLE,
        self::BSSUPERADMIN_ROLE,
        self::BSMECHANIC_ROLE,
    ];

    public const COMPANY_ROLES = [
        self::DISPATCHER_ROLE,
        self::DRIVER_ROLE,
        self::ADMIN_ROLE,
        self::ACCOUNTANT_ROLE,
        self::SUPERADMIN_ROLE,
        self::SUPERDRIVER_ROLE,
        self::OWNER_ROLE,
        self::OWNER_DRIVER_ROLE,
    ];

    public const DRIVER_ROLES = [
        self::DRIVER_ROLE,
        self::OWNER_DRIVER_ROLE,
    ];

    public const OWNER_ROLES = [
        self::OWNER_ROLE,
        self::OWNER_DRIVER_ROLE,
    ];

    public const ATTACHMENT_FIELD_NAME = 'attachment_files';

    public const ATTACHMENT_COLLECTION_NAME = 'attachments';

    public const GUARD = 'api';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = self::TABLE_NAME;

    protected string $guard_name = 'api';

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'password',
        'first_name',
        'last_name',
        'phone',
        'email',
        'status',
        'owner_id',
        'phones',
        'phone_extension',
        'can_check_orders',
    ];

    protected $attributes = ['status' => self::STATUS_ACTIVE];

    protected $hidden = ['password'];

    protected $casts = [
        'can_check_orders' => 'boolean',
        'phones' => 'array'
    ];

    protected $dates = ['deleted_at'];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(
            function (self $model) {
                $model->setCompanyId();
            }
        );
    }

    public function carrier(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'carrier_id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(User::class, 'owner_id', 'id');
    }

    public function generatePassword(): string
    {
        $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');

        return substr($random, 0, 10);
    }

    public function changeLanguage(string $languageAlias): bool
    {
        if ($languageAlias === $this->language) {
            return true;
        }
        $this->language = $languageAlias;
        return $this->save();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new MailResetPasswordToken($token, $this, $this->password));
    }

    public function isAdmin(): bool
    {
        return in_array($this->getRoleName(), [self::SUPERADMIN_ROLE, self::SUPERDRIVER_ROLE, self::ADMIN_ROLE]);
    }

    public function isAdminRole(): bool
    {
        return in_array($this->getRoleName(), [self::ADMIN_ROLE]);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->getRoleName(), [self::SUPERADMIN_ROLE]);
    }

    public function isDispatcher(): bool
    {
        return in_array($this->getRoleName(), [self::DISPATCHER_ROLE]);
    }

    public function isDriver(): bool
    {
        return in_array($this->getRoleName(), self::DRIVER_ROLES);
    }

    public function getRoleName(): string
    {
        return $this->getRoleNames()->first();
    }

    public function modelFilter(): string
    {
        return UserFilter::class;
    }

    public function getImageClass(): string
    {
        return UserProfileImage::class;
    }

    public function routeNotificationForFcm(): ?string
    {
        return $this->fcm_token;
    }

    public function getAttachments(): array
    {
        return $this
            ->getMedia(self::ATTACHMENT_COLLECTION_NAME)
            ->all();
    }

    public function isBroker(): bool
    {
        return (bool)$this->broker_id;
    }

    public function isCarrier(): bool
    {
        return (bool)$this->carrier_id;
    }

    public function getCompany(): ?Company
    {
        if ($this->broker_id) {
            return Company::find($this->broker_id);
        }

        if ($this->carrier_id) {
            return Company::find($this->carrier_id);
        }

        return null;
    }

    public function getCompanyId(): int
    {
        if ($this->broker_id) {
            return Company::find($this->broker_id)->getCompanyId();
        }

        if ($this->carrier_id) {
            return Company::find($this->carrier_id)->getCompanyId();
        }

        return 0;
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function toggleStatus()
    {
        $this->status = ($this->status === self::STATUS_ACTIVE)
            ? self::STATUS_INACTIVE
            : self::STATUS_ACTIVE;
        $this->save();
    }

    public function isBodyShopUser(): bool
    {
        return in_array($this->getRoleName(), self::BS_ROLES);
    }

    public function truck(): ?HasOne
    {
        return $this->hasOne(Truck::class, 'driver_id');
    }

    public function trailer(): ?HasOne
    {
        return $this->hasOne(Trailer::class, 'driver_id');
    }

    public function ownerTrucks(): HasMany
    {
        return $this->hasMany(Truck::class, 'owner_id');
    }

    public function fuelCards(): HasManyThrough
    {
        return $this->hasManyThrough(
            FuelCard::class,
            FuelCardHistory::class,
            'user_id',
            'id',
            'id',
            'fuel_card_id',
        )
            ->where(FuelCardHistory::TABLE_NAME . '.active', true)
            ->orderBy(FuelCardHistory::TABLE_NAME . '.id');
    }

    public function ownerTrailers(): HasMany
    {
        return $this->hasMany(Trailer::class, 'owner_id');
    }

    public function isOwner(): bool
    {
        return in_array($this->getRoleName(), self::OWNER_ROLES);
    }

    public function isMechanic(): bool
    {
        return $this->getRoleName() === self::BSMECHANIC_ROLE;
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

    public function comments(): HasMany
    {
        return $this->hasMany(UserComment::class, 'user_id', 'id');
    }

    public function driverTrucksHistory(): HasMany
    {
        return $this->hasMany(TruckDriverHistory::class, 'driver_id');
    }

    public function driverTrailersHistory(): HasMany
    {
        return $this->hasMany(TrailerDriverHistory::class, 'driver_id');
    }

    public function ownerTrucksHistory(): HasMany
    {
        return $this->hasMany(TruckOwnerHistory::class, 'owner_id');
    }

    public function ownerTrailersHistory(): HasMany
    {
        return $this->hasMany(TrailerOwnerHistory::class, 'owner_id');
    }

    public function hasDriverTrucksHistory(): bool
    {
        $currentCount = 0;

        if ($this->truck) {
            $currentCount += $this->driverTrucksHistory()
                ->where('truck_id', $this->truck->id)
                ->whereNull('unassigned_at')
                ->count();
        }

        return $this->driverTrucksHistory()->count() - $currentCount > 0;
    }

    public function hasDriverTrailersHistory(): bool
    {
        $currentCount = 0;

        if ($this->trailer) {
            $currentCount += $this->driverTrailersHistory()
                ->where('trailer_id', $this->trailer->id)
                ->whereNull('unassigned_at')
                ->count();
        }

        return $this->driverTrailersHistory()->count() - $currentCount > 0;
    }

    public function hasOwnerTrucksHistory(): bool
    {
        $currentCount = 0;

        if ($this->ownerTrucks->count()) {
            $currentCount += $this->ownerTrucksHistory()
                ->whereIn('truck_id', $this->ownerTrucks->pluck('id')->toArray())
                ->whereNull('unassigned_at')
                ->count();
        }

        return $this->ownerTrucksHistory()->count() - $currentCount > 0;
    }

    public function hasOwnerTrailersHistory(): bool
    {
        $currentCount = 0;

        if ($this->ownerTrailers->count()) {
            $currentCount += $this->ownerTrailersHistory()
                ->whereIn('trailer_id', $this->ownerTrailers->pluck('id')->toArray())
                ->whereNull('unassigned_at')
                ->count();
        }

        return $this->ownerTrailersHistory()->count() - $currentCount > 0;
    }

    public function driverLicenses(): HasMany
    {
        return $this->hasMany(DriverLicense::class, 'driver_id');
    }

    public function getCurrentDriverLicense(): ?DriverLicense
    {
        return $this->driverLicenses()->current()->first();
    }

    public function getPreviousDriverLicense(): ?DriverLicense
    {
        return $this->driverLicenses()->previous()->first();
    }

    public function getRelationsForDiff(): array
    {
        return [
            'driverInformation' => $this->driverInfo,
            self::ATTACHMENT_COLLECTION_NAME => (
                    new MediaCollection($this->getMedia(self::ATTACHMENT_COLLECTION_NAME))
                )->getAttributesForDiff(),
            'comments' => $this->comments,
            'avatar' => (new MediaCollection($this->getMedia()))->getAttributesForDiff(),
            'driverLicense' => $this->driverLicenses->where('type', DriverLicense::TYPE_CURRENT)->first(),
            'previousDriverLicense' => $this->driverLicenses->where('type', DriverLicense::TYPE_PREVIOUS)->first(),
            'tags' => $this->tags,
            'phones' => $this->phones,
        ];
    }

    public function getAttributesForDiff(): array
    {
        $resultGetDirty = $this->getAttributes();
        $resultGetDirty['role'] = $this->getRoleName();

        foreach ($this->getRelationsForDiff() as $relationName => $relation) {
            if(is_array($relation)) {
                $resultGetDirty[$relationName] = $relation;
            } elseif($relation) {
                $resultGetDirty[$relationName] = $relation->getAttributesForDiff();
            }
        }

        return $resultGetDirty;
    }
}
