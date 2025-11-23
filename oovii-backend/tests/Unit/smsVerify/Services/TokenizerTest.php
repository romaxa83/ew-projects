<?php

namespace Tests\Unit\smsVerify\Services;

use InvalidArgumentException;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use WezomCms\SmsVerify\Services\Tokenizer;
use WezomCms\SmsVerify\ValueObj\Token;

class TokenizerTest extends TestCase
{
    /** @test */
    public function generate_sms_token()
    {
        \Config::set('sms.verify.sms_token_expired', 'PT1H');

        $date = CarbonImmutable::now();
        $token = Tokenizer::generateSmsToken($date);

        $this->assertTrue($token instanceof Token);
        $this->assertNotEmpty($token->getValue());
        $this->assertNotEmpty($token->getExpires());
    }


}

