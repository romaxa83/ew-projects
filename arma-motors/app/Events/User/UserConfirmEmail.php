<?php

namespace App\Events\User;

use App\Models\User\User;
use Illuminate\Queue\SerializesModels;

// пользователь потвердил почту
class UserConfirmEmail
{
    use SerializesModels;

    /**
     * @param User $user
     */
    public function __construct(
        public User $user,
    )
    {}
}
