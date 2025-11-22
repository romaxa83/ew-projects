<?php

namespace App\Models\Admins;

use App\ModelFilters\Saas\Admins\AdminFilter;
use App\Models\BaseAuthenticatable;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Files\UserProfileImage;
use App\Notifications\Saas\Admins\AdminMailResetPasswordToken;
use App\Traits\Permissions\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @property int id
 * @property bool status
 * @property string full_name
 * @property string phone
 * @property string email
 * @property string password
 */
class Admin extends BaseAuthenticatable implements HasMedia
{
    use HasApiTokens;
    use HasMediaTrait;
    use HasRoles;
    use Notifiable;

    public const TABLE = 'admins';

    public const GUARD = 'api_admin';

    protected $table = self::TABLE;

    protected $fillable = [
        'password',
        'email',
        'full_name',
        'phone',
        'status',
    ];

    protected $attributes = ['status' => true];

    protected $hidden = ['password'];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function modelFilter(): string
    {
        return AdminFilter::class;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AdminMailResetPasswordToken($token, $this));
    }

    public function changeLanguage(string $languageAlias): bool
    {
        if ($languageAlias === $this->language) {
            return true;
        }
        $this->language = $languageAlias;
        return $this->save();
    }

    public function getImageClass(): string
    {
        return UserProfileImage::class;
    }
}
