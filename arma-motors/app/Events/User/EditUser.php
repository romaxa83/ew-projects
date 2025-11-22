<?php

namespace App\Events\User;

use App\Models\User\User;
use Illuminate\Queue\SerializesModels;

// пользователь отредактирован
class EditUser
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

