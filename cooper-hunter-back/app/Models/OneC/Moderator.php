<?php

namespace App\Models\OneC;

use App\Casts\EmailCast;
use App\Contracts\Roles\HasGuardUser;
use App\Contracts\Roles\HasRolesContract;
use App\Models\BaseAuthenticatable;
use App\Models\ListPermission;
use App\Traits\HasFactory;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Database\Factories\OneC\ModeratorFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string name
 * @property Email email
 * @property string password
 *
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static ModeratorFactory factory(...$parameters)
 */
class Moderator extends BaseAuthenticatable implements ListPermission, HasRolesContract, HasGuardUser
{
    use HasFactory;
    use HasRoles;
    use SetPasswordTrait;
    use DefaultListPermissionTrait;

    public const TABLE = 'moderators';

    public const GUARD = 'graph_1c_moderator';

    public const MORPH_NAME = 'moderator';

    protected $table = self::TABLE;

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $casts = [
        'email' => EmailCast::class,
    ];

    public function getGuardName(): string
    {
        return self::GUARD;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
