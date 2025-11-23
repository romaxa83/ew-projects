<?php

namespace WezomCms\SmsVerify\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\SmsVerify\Models\SmsVerify;

class SendSmsCode
{
    use SerializesModels;

    public function __construct(public SmsVerify $smsVerify)
    {}
}
