<?php


namespace App\Exceptions\Admins;


use Core\Exceptions\TranslatedException;

class AdminUniqPhoneEmailException extends TranslatedException
{
    public function __construct(string $admin)
    {
        parent::__construct(trans('validation.custom.admins.uniq_email', ['admin' => $admin]));
    }
}
