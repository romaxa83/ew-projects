<?php

namespace App\Broadcasting\Events\User;

class UpdateUserBroadcast extends UserBroadcast
{
    public const NAME = 'user.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
