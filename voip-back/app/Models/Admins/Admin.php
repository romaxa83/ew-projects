<?php

namespace App\Models\Admins;

use App\Casts\EmailCast;
use App\Contracts\Media\HasMedia;
use App\Filters\Admins\AdminFilter;
use App\Models\BaseAuthenticatable;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Traits\Filterable;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Model\ActiveTrait;
use App\Traits\Model\LoginDataTrait;
use App\Traits\Model\Media\InteractsWithMedia;
use App\Traits\Model\NotifyTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Core\Contracts\HasAvatar;
use Core\Traits\Models\InteractsWithAvatar;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Admins\AdminFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property string name
 * @property string password
 * @property string lang
 * @property bool active
 *
 * @property Email email
 * @property null|string email_verification_code
 * @property null|Carbon email_verified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see Admin::relationAdmins()
 * @property-read Collection|Admin[] relationAdmins
 *
 * @see Admin::scopeWithoutAdmin()
 * @method static withoutAdmin(int|string $id)
 *
 * @method Builder|static whereEmail($email)
 * @method static AdminFactory factory(...$parameters)
 */
class Admin extends BaseAuthenticatable implements
    Languageable, ListPermission, Subscribable, HasAvatar, HasMedia
{
    use HasFactory;
    use Filterable;
    use Notifiable;
    use HasRoles;
    use SetPasswordTrait;
    use LanguageRelation;
    use DefaultListPermissionTrait;
    use ActiveTrait;
    use NotifyTrait;
    use InteractsWithAvatar;
    use InteractsWithMedia;
    use SoftDeletes;
    use LoginDataTrait;

    public const GUARD = 'graph_admin';

    public const MORPH_NAME = 'admin';

    public const TABLE = 'admins';

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
        'active' => 'boolean',
        'notify' => 'boolean',
    ];

    public function modelFilter(): string
    {
        return AdminFilter::class;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes($this->mimeImage());
    }


    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }
    public function scopeWithoutAdmin(Builder|self $b, int|string $id): void
    {
        $b->where(static::TABLE . '.id', '!=', $id);
    }
}
