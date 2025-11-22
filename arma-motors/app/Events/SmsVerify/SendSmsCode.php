<?php

namespace App\Events\SmsVerify;

use App\Models\Verify\SmsVerify;
use Illuminate\Queue\SerializesModels;

class SendSmsCode
{
    use SerializesModels;

    /**
     * SendCode constructor.
     * @param SmsVerify $smsVerify
     */
    public function __construct(public SmsVerify $smsVerify)
    {}
}
