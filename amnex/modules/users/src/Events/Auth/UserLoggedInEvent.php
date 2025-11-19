<?php

namespace Wezom\Users\Events\Auth;

use Wezom\Core\Models\Auth\GuestSession;
use Wezom\Users\Models\User;

class UserLoggedInEvent
{
    /**
     * Logged in user
     */
    protected User $user;

    /**
     * A guest session that the user had before authorization.
     */
    protected ?GuestSession $session;

    public function __construct(User $user, ?GuestSession $session = null)
    {
        $this->user = $user;
        $this->session = $session;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSession(): ?GuestSession
    {
        return $this->session;
    }
}
