<?php

namespace App\Events\User;

use App\Models\User\User;
use Illuminate\Queue\SerializesModels;

// если по запросу к АА приходит ничего

class NotUserFromAA
{
    use SerializesModels;

    public function __construct(
        public User $user
    )
    {}
}
