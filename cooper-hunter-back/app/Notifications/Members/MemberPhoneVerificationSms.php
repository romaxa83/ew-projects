<?php

namespace App\Notifications\Members;

use Core\Contracts\Sms\Smsable;
use Illuminate\Contracts\Queue\ShouldQueue;

class MemberPhoneVerificationSms implements Smsable, ShouldQueue
{
    public function __construct(public string $code)
    {
    }

    public function body(): string
    {
        return __('messages.sms.auth_code', ['code' => $this->code]);
    }
}
