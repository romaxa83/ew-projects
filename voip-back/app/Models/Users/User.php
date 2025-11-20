<?php

namespace App\Models\Users;

use App\Models\BaseAuthenticatable;
use App\Traits\AddSelectTrait;
use App\Traits\Filterable;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

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
 */
class User extends BaseAuthenticatable
{
    use HasRoles;
    use Filterable;
    use Notifiable;
    use SetPasswordTrait;
    use SetLanguageTrait;
    use LanguageRelation;
    use AddSelectTrait;
    use DefaultListPermissionTrait;
    use SoftDeletes;

    public const GUARD = 'graph_user';
    public const MIN_LENGTH_PASSWORD = 10;

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

    protected $casts = [];

    public function getName(): string
    {
        $fullName = sprintf('%s %s', $this->first_name, $this->last_name);

        return str_replace(' ', ' ', $fullName);
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}

