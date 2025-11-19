<?php

namespace Wezom\Users\Events\Auth;

use Wezom\Users\Models\User;

class UserRegisteredEvent
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
