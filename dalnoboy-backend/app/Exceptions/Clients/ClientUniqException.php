<?php


namespace App\Exceptions\Clients;


use Core\Exceptions\TranslatedException;

class ClientUniqException extends TranslatedException
{
    public function __construct()
    {
        parent::__construct(trans('validation.custom.clients.uniq'));
    }
}
