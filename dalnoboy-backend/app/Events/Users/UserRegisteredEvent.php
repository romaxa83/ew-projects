<?php

namespace App\Events\Users;

use App\Models\Users\User;

class UserRegisteredEvent
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
