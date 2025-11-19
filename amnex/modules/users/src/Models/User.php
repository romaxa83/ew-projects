<?php

namespace Wezom\Users\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Wezom\Core\Enums\RoleEnum;
use Wezom\Core\Exceptions\TranslatedException;
use Wezom\Core\Models\Auth\BaseAuthenticatable;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalSession;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Traits\Model\ActiveScopeTrait;

/**
 * \Wezom\Users\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $email_verification_code
 * @property Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $password_reset_code
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Role|null $role
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static Builder|User active(array|bool $value = true)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions, $without = false)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null, $without = false)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerificationCode($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePasswordResetCode($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRoles(RoleEnum|array|string $roles, ?string $guard = null)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withoutPermission($permissions)
 * @method static Builder|User withoutRole($roles, $guard = null)
 * @mixin Eloquent
 */
class User extends BaseAuthenticatable
{
    use ActiveScopeTrait;
    use HasFactory;

    public const GUARD = 'graph_user';
    public const TABLE = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function findByEmail(string $email): ?self
    {
        return self::query()->where('email', $email)->first();
    }

    public static function findByEmailOrFail(string $email): self
    {
        $user = static::findByEmail($email);

        if (! $user) {
            throw new TranslatedException(__('users::exceptions.user_not_found'));
        }

        return $user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return trim(sprintf('%s %s', $this->first_name, $this->last_name));
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getPasswordResetCode(): ?string
    {
        return $this->password_reset_code;
    }

    public function setPasswordResetCode(?string $code): void
    {
        $this->password_reset_code = $code;
    }

    public function setVerificationCode(?string $code): void
    {
        $this->email_verification_code = $code;
    }

    public function setEmailVerifiedAt(?Carbon $at): void
    {
        $this->email_verified_at = $at;
    }

    public function isEmailVerified(): bool
    {
        return (bool)$this->email_verified_at;
    }
}
