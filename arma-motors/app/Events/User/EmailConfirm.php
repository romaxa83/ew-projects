<?php

namespace App\Events\User;

use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use Illuminate\Queue\SerializesModels;

class EmailConfirm
{
    use SerializesModels;

    /**
     * @param User $user
     * @param EmailVerify $emailVerify
     */
    public function __construct(
        public User $user,
        public EmailVerify $emailVerify
    )
    {}
}
