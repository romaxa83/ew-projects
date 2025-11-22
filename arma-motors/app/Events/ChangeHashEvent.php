<?php

namespace App\Events;

use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use Illuminate\Queue\SerializesModels;

class ChangeHashEvent
{
    use SerializesModels;

    public function __construct(
        public string $alias
    )
    {}
}
