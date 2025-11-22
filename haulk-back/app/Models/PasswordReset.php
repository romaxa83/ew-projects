<?php

namespace App\Models;

use Eloquent;

/**
 * @property string email
 * @property string token
 * @property string created_at
 *
 * @mixin Eloquent
 */
class PasswordReset extends BaseModel
{
    public const TABLE = 'password_resets';

    public $timestamps = false;

    protected $primaryKey = 'email';
}
