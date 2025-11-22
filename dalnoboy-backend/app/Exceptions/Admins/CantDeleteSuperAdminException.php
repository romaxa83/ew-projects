<?php

namespace App\Exceptions\Admins;

use Core\Exceptions\TranslatedException;

class CantDeleteSuperAdminException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.admins.can_not_delete_super_admin'));
    }
}
