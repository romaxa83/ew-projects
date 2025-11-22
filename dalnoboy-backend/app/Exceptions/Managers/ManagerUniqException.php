<?php


namespace App\Exceptions\Managers;


use Core\Exceptions\TranslatedException;

class ManagerUniqException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.managers.uniq'));
    }
}
