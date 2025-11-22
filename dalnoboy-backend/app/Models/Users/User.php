<?php

namespace App\Models\Users;

use App\Casts\EmailCast;
use App\Contracts\Models\HasGuard;
use App\Enums\Permissions\GuardsEnum;
use App\Enums\Users\AuthorizationExpirationPeriodEnum;
use App\Filters\Users\UserFilter;
use App\Models\BasicAuthenticatable;
use App\Models\Branches\Branch;
use App\Models\Inspections\Inspection;
use App\Models\Languageable;
use App\Models\Media\Media;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Model\HasPhones;
use App\Traits\Model\InteractsWithMedia;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Kirschbaum\PowerJoins\PowerJoins;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @method static UserFactory factory(...$options)
 *
 * @property int id
 * @property string first_name
 * @property string last_name
 * @property string second_name
 * @property Email email
 * @property null|Carbon email_verified_at
 *
 * @mixin Model
 */
class User extends BasicAuthenticatable implements Languageable, Subscribable, HasGuard, HasMedia
{
    use HasFactory;
    use HasRoles;
    use Filterable;
    use HasApiTokens;
    use Notifiable;
    use SetPasswordTrait;
    use LanguageRelation;
    use PowerJoins;
    use HasPhones;
    use InteractsWithMedia;

    public const GUARD = GuardsEnum::USER;

    public const MIN_LENGTH_PASSWORD = 8;

    public const TABLE = 'users';

    public const MC_AVATAR = 'avatar';

    public const ALLOWED_SORTING_FIELDS = [
        'full_name',
        'branch_name'
    ];

    public const ALLOWED_SORTING_FIELDS_RELATIONS = [
        'branch_name' => 'branch.name',
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'second_name',
        'lang',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'authorization_expiration_period' => AuthorizationExpirationPeriodEnum::class,
    ];

    public function branch(): HasOneThrough
    {
        return $this->hasOneThrough(
            Branch::class,
            UserBranch::class,
            'user_id',
            'id',
            'id',
            'branch_id'
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(UserFilter::class);
    }

    public function routeNotificationForMail(Notification $notification): array
    {
        return [
            $this->getEmailString() => $this->getName()
        ];
    }

    public function getEmailString(): string
    {
        return (string)$this->getEmail();
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): string
    {
        $fullName = sprintf('%s %s %s', $this->last_name, $this->first_name, $this->second_name);

        return trim($fullName);
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

    public function getGuard(): string
    {
        return self::GUARD;
    }

    public function getAvatarAttribute(): Media|BaseMedia|null
    {
        return $this->getFirstMedia(User::MC_AVATAR);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'inspector_id', 'id')
            ->orderByDesc('id');
    }
}
