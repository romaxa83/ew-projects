<?php

namespace App\Models\Admins;

use App\Casts\EmailCast;
use App\Filters\Admins\AdminFilter;
use App\Models\BaseAuthenticatable;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Traits\Filterable;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Admins\AdminFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * @property int id
 * @property string name
 * @property string password
 * @property string lang
 *
 * @property Email email
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method Builder|static whereEmail($email)
 * @method static AdminFactory factory(...$parameters)
 */
class Admin extends BaseAuthenticatable implements Languageable, ListPermission, Subscribable
{
    use HasFactory;
    use Filterable;
    use Notifiable;
    use HasRoles;
    use SetPasswordTrait;
    use LanguageRelation;
    use DefaultListPermissionTrait;

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
    ];

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function modelFilter(): string
    {
        return AdminFilter::class;
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
}
