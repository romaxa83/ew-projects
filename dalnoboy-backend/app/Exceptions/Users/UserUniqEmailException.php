<?php


namespace App\Exceptions\Users;


use Core\Exceptions\TranslatedException;

class UserUniqEmailException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.users.uniq_email'));
    }
}
