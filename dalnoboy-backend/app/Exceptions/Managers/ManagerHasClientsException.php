<?php


namespace App\Exceptions\Managers;


use Core\Exceptions\TranslatedException;

class ManagerHasClientsException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.managers.has_clients'));
    }
}
