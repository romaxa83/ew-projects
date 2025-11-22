<?php

namespace App\Entities\Users;

use App\Models\Users\User;

class UserStateEntity
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
