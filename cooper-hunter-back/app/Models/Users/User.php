<?php

namespace App\Models\Users;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Members\HasFavourites;
use App\Contracts\Members\HasPhoneNumber;
use App\Contracts\Members\Member;
use App\Entities\Messages\AlertMessageEntity;
use App\Entities\Users\UserStateEntity;
use App\Events\Users\UserUpdatedEvent;
use App\Filters\Users\UserFilter;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\BaseAuthenticatable;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Fcm\FcmToken;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Models\Projects\Project;
use App\Permissions\Users\UserDeletePermission;
use App\Permissions\Users\UserUpdatePermission;
use App\Traits\AddSelectTrait;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Model\Favourites\HasFavouritesTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Core\Services\AlertMessages\CustomAlertMessageService;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;

/**
 * @property int id
 * @property string|null guid
 * @property string first_name
 * @property string last_name
 * @property string password
 *
 * @property null|string email_verification_code
 *
 * @property Email email
 * @property Phone|null phone
 * @property Carbon|null email_verified_at
 * @property Carbon|null phone_verified_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null deleted_at
 *
 * @see User::projects()
 * @property-read Collection|Project[] projects
 *
 * @see User::favourites()
 * @property-read Collection|Favourite[] favourites
 *
 * @see User::scopeNew()
 * @method Builder|static new()
 *
 * @method static UserFactory factory(...$options)
 */
class User extends BaseAuthenticatable implements
    Languageable,
    ListPermission,
    Subscribable,
    Member,
    HasFavourites,
    HasPhoneNumber,
    AlertModel
{
    use HasFactory;
    use HasRoles;
    use Filterable;
    use Notifiable;
    use SetPasswordTrait;
    use SetLanguageTrait;
    use LanguageRelation;
    use AddSelectTrait;
    use DefaultListPermissionTrait;
    use SoftDeletes;
    use HasFavouritesTrait;

    public const ONEC_TYPE = 'owner';

    public const GUARD = 'graph_user';

    public const MORPH_NAME = 'user';

    public const TABLE = 'users';

    public const ALLOWED_SORTING_FIELDS = [
        'name',
        'email',
        'roles',
        'created_at',
    ];

    public const ALLOWED_SORTING_FIELDS_RELATIONS = [
        'roles' => 'roles.translation.title',
    ];

    protected static array $eagerLoadingFields = [
        'language' => 'lang',
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'email',
        'password',
        'lang',
        'first_name',
        'last_name',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
    ];

    protected $dispatchesEvents = [
        'updated' => UserUpdatedEvent::class,
    ];

    public function getGuardName(): string
    {
        return self::GUARD;
    }

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function projects(): MorphMany|Project
    {
        return $this->morphMany(Project::class, 'member');
    }

    public function alerts(): MorphToMany|Collection
    {
        return $this->morphToMany(
            Alert::class,
            'recipient',
            AlertRecipient::TABLE
        )
            ->withPivot('is_read');
    }

    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'member');
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function modelFilter(): string
    {
        return UserFilter::class;
    }

    public function getEmailString(): string
    {
        return (string)$this->getEmail();
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhoneString(): string
    {
        return (string)$this->phone;
    }

    public function phoneVerified(): bool
    {
        return (bool)$this->phone_verified_at;
    }

    public function getName(): string
    {
        $fullName = sprintf('%s %s', $this->first_name, $this->last_name);

        return str_replace(' ', ' ', $fullName);
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        $result['active'] = $this->isActive();

        return $result;
    }

    public function isActive(): bool
    {
        return $this->isEmailVerified();
    }

    public function isEmailVerified(): bool
    {
        return (bool)$this->email_verified_at;
    }

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }

    public function canBeDeleted(): bool
    {
        return true;
    }

    public function getDeletePermissionKey(): string
    {
        return UserDeletePermission::KEY;
    }

    public function getUpdatePermissionKey(): string
    {
        return UserUpdatePermission::KEY;
    }

    public function getState(): UserStateEntity
    {
        return new UserStateEntity($this);
    }

    /**
     * @return BaseCollection<AlertMessageEntity>
     */
    public function getAlerts(): BaseCollection
    {
        return app(CustomAlertMessageService::class)->getForUser($this);
    }

    public function scopeNew(Builder|self $builder): void
    {
        $builder->whereNull('guid');
    }

    public function routeNotificationForFcm(): ?array
    {
        $tokens = $this->fcmTokens->pluck('token');

        if ($tokens->isEmpty()) {
            return null;
        }
        return $tokens->toArray();
    }
}
