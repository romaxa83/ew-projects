<?php

namespace Wezom\Core\Traits\Model;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Wezom\Core\Traits\Permissions\HasRoles;
use Wezom\Core\Traits\SetPasswordTrait;

trait Authenticatable
{
    use ActiveScopeTrait;
    use HasApiTokens;
    use HasRoles;
    use Notifiable;
    use SetPasswordTrait;
}
