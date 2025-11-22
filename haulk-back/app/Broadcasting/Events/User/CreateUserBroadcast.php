<?php

namespace App\Broadcasting\Events\User;

class CreateUserBroadcast extends UserBroadcast
{
    public const NAME = 'user.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
