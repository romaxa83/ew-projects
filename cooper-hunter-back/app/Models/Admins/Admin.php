<?php

namespace App\Models\Admins;

use App\Casts\EmailCast;
use App\Contracts\Alerts\AlertModel;
use App\Contracts\Media\HasMedia;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Roles\HasRolesContract;
use App\Filters\Admins\AdminFilter;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\BaseAuthenticatable;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Traits\Filterable;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Permissions\ChatMessagingPermission;
use Core\Chat\Traits\InteractsWithChat;
use Core\Contracts\HasAvatar;
use Core\Traits\Models\InteractsWithAvatar;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Admins\AdminFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property string name
 * @property Email email
 * @property string password
 * @property string|null lang
 * @property string|null email_verification_code
 * @property Carbon|null email_verified_at
 *
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method Builder|static whereEmail($email)
 * @method static AdminFactory factory(...$parameters)
 */
class Admin extends BaseAuthenticatable implements Languageable, ListPermission, Subscribable, HasRolesContract,
                                                   HasGuardUser, Messageable, HasAvatar, HasMedia, AlertModel
{
    use HasFactory;
    use Filterable;
    use Notifiable;
    use HasRoles;
    use SetPasswordTrait;
    use LanguageRelation;
    use DefaultListPermissionTrait;
    use InteractsWithChat;
    use InteractsWithAvatar;
    use InteractsWithMedia;

    public const GUARD = 'graph_admin';
    public const MORPH_NAME = 'admin';
    public const TABLE = 'admins';

    public const CONVERSIONS = [
        'small' => [
            'width' => 300,
        ],
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'name',
        'email',
        'created_at'
    ];

    protected $table = self::TABLE;

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
    ];

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    /**
     * @return array<self>
     */
    public static function getChatAdmins(): Collection
    {
        return self::query()
            ->whereHas(
                'roles',
                static fn(Builder $b) => $b
                    ->where(
                        [
                            'name' => config('permission.roles.super_admin'),
                            'guard_name' => self::GUARD,
                        ]
                    )
                    ->orWhereHas(
                        'permissions', static fn(Builder $b1) => $b1
                        ->where(
                            [
                                'name' => ChatMessagingPermission::KEY,
                                'guard_name' => self::GUARD,
                            ]
                        )
                    )
            )
            ->get();
    }

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

    public function alerts(): MorphToMany|Collection
    {
        return $this->morphToMany(
            Alert::class,
            'recipient',
            AlertRecipient::TABLE
        )
            ->withPivot('is_read');
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
        return AdminFilter::class;
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
        return $this->name;
    }

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function isEmailVerified(): bool
    {
        return (bool)$this->email_verified_at;
    }

    public function routeNotificationForFcm(): ?array
    {
        return null;
    }
}
