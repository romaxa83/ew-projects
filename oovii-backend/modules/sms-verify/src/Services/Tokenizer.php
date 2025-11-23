<?php

namespace WezomCms\SmsVerify\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Ramsey\Uuid\Uuid;
use WezomCms\SmsVerify\ValueObj\Token;

class Tokenizer
{
    private function __construct()
    {}

    public static function generateSmsToken(null|CarbonImmutable $now = null): Token
    {
        $interval = new CarbonInterval(config('cms.sms-verify.config.verify.sms_token_expired'));
        if(!$now){
            $now = CarbonImmutable::now();
        }

        return new Token(
            Uuid::uuid4()->toString(),
            $now->add($interval)
        );
    }

    public static function generateActionToken(null|CarbonImmutable $now = null): Token
    {
        $interval = new CarbonInterval(config('cms.sms-verify.config.verify.action_token_expired'));
        if(!$now){
            $now = CarbonImmutable::now();
        }

        return new Token(
            Uuid::uuid4()->toString(),
            $now->add($interval)
        );
    }
}
