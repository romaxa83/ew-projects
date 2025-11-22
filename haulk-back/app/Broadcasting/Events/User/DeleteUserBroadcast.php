<?php

namespace App\Broadcasting\Events\User;

class DeleteUserBroadcast extends UserBroadcast
{
    public const NAME = 'user.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
