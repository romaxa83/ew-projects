<?php

namespace App\Models\Admins;

use App\Casts\EmailCast;
use App\Contracts\Models\HasGuard;
use App\Enums\Permissions\GuardsEnum;
use App\Filters\Admins\AdminFilter;
use App\Models\BasicAuthenticatable;
use App\Models\Languageable;
use App\Traits\Filterable;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Model\HasPhones;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Admins\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Passport\HasApiTokens;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $second_name
 * @property Email $email
 *
 * @method static AdminFactory factory(...$parameters)
 */
class Admin extends BasicAuthenticatable implements Languageable, Subscribable, HasGuard
{
    use HasFactory;
    use HasApiTokens;
    use Filterable;
    use Notifiable;
    use HasRoles;
    use SetPasswordTrait;
    use LanguageRelation;
    use HasPhones;

    public const GUARD = GuardsEnum::ADMIN;

    public const TABLE = 'admins';

    public const ALLOWED_SORTING_FIELDS = [
        'id',
        'full_name',
        'email',
        'created_at'
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'first_name',
        'last_name',
        'second_name',
        'email',
        'password',
        'lang',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email' => EmailCast::class,
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(AdminFilter::class);
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

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }

    public function getGuard(): string
    {
        return self::GUARD;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
