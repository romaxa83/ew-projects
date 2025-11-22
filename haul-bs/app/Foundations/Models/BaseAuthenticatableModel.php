<?php

namespace App\Foundations\Models;

use App\Foundations\Modules\Permission\Traits\HasRoles;
use App\Traits\Models\SetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Laravel\Passport\HasApiTokens;

/**
 * @mixin Model
 */
abstract class BaseAuthenticatableModel extends User
{
    use HasApiTokens;
    use SetPassword;
    use HasRoles;

    public const MIN_LENGTH_PASSWORD = 8;
}

