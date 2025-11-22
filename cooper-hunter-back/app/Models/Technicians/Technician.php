<?php

namespace App\Models\Technicians;

use App\Casts\EmailCast;
use App\Casts\PhoneCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Media\HasMedia;
use App\Contracts\Members\HasCommercialProjects;
use App\Contracts\Members\HasFavourites;
use App\Contracts\Members\HasPhoneNumber;
use App\Contracts\Members\Member;
use App\Events\Technicians\TechnicianUpdatedEvent;
use App\Exceptions\Technicians\TechnicianLicenseIsMissingException;
use App\Filters\Technicians\TechnicianFilter;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\BaseAuthenticatable;
use App\Models\Fcm\FcmToken;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Projects\Project;
use App\Models\Support\SupportRequest;
use App\Permissions\Technicians\TechnicianDeletePermission;
use App\Permissions\Technicians\TechnicianUpdatePermission;
use App\Traits\AddSelectTrait;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Model\Commercial\InteractsWithCommercialProjects;
use App\Traits\Model\Favourites\HasFavouritesTrait;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Traits\InteractsWithChat;
use Core\Contracts\HasAvatar;
use Core\Traits\Models\InteractsWithAvatar;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Technicians\TechnicianFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string|null guid
 * @property bool is_certified
 * @property bool is_verified
 * @property bool is_commercial_certification
 * @property int state_id
 * @property int|null country_id
 * @property string|null hvac_license
 * @property string|null epa_license
 * @property string first_name
 * @property string last_name
 * @property Email email
 * @property Phone|null phone
 * @property Carbon|null email_verified_at
 * @property Carbon|null phone_verified_at
 * @property string|null email_verification_code
 * @property string password
 * @property string|null remember_token
 * @property string|null lang
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null deleted_at
 *
 * @see Technician::state()
 * @property-read State state
 *
 * @see Technician::country()
 * @property-read Country country
 *
 * @see User::projects()
 * @property-read Collection|Project[] projects
 *
 * @see Technician::scopeNew()
 * @method Builder|static new()
 *
 * @method static TechnicianFactory factory(...$options)
 */
class Technician extends BaseAuthenticatable implements Languageable, ListPermission, Subscribable, Member,
                                                        HasFavourites, HasPhoneNumber, AlertModel, Messageable,
                                                        HasAvatar, HasMedia, HasCommercialProjects
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
    use InteractsWithChat;
    use InteractsWithAvatar;
    use InteractsWithMedia;
    use InteractsWithCommercialProjects;

    public const ONEC_TYPE = 'tech';

    public const GUARD = 'graph_technician';
    public const MORPH_NAME = 'technician';
    public const TABLE = 'technicians';

    public const CONVERSIONS = [
        'small' => [
            'width' => 300,
        ],
    ];

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
        'country_id',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_certified' => 'boolean',
        'is_verified' => 'boolean',
        'is_commercial_certification' => 'boolean',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'email' => EmailCast::class,
        'phone' => PhoneCast::class,
    ];

    protected $dispatchesEvents = [
        'updated' => TechnicianUpdatedEvent::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes($this->mimeImage());
    }

    public function getGuardName(): string
    {
        return self::GUARD;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function state(): BelongsTo|State
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo|Country
    {
        return $this->belongsTo(Country::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function supportRequests(): HasMany
    {
        return $this->hasMany(SupportRequest::class);
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

    public function getLicense(): string
    {
        return match (true) {
            $this->isHvacLicense() => $this->hvac_license,
            $this->isEpaLicense() => $this->epa_license,
            default => '',
        };
    }

    public function isHvacLicense(): bool
    {
        return $this->state->hvac_license;
    }

    public function isEpaLicense(): bool
    {
        return $this->state->epa_license;
    }

    public function isCommercialCertification(): bool
    {
        return $this->is_commercial_certification;
    }

    /** @throws TechnicianLicenseIsMissingException */
    public function setLicense(string $license): self
    {
        if ($this->relationLoaded('state')) {
            $this->unsetRelation('state');
        }

        if ($this->isHvacLicense()) {
            $this->hvac_license = $license;

            return $this;
        }

        if ($this->isEpaLicense()) {
            $this->epa_license = $license;

            return $this;
        }

        throw new TechnicianLicenseIsMissingException();
    }

    public function modelFilter(): string
    {
        return TechnicianFilter::class;
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

    public function isModerated(): bool
    {
        return $this->isEmailVerified() && $this->is_verified;
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
        return TechnicianDeletePermission::KEY;
    }

    public function getUpdatePermissionKey(): string
    {
        return TechnicianUpdatePermission::KEY;
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

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
