<?php

namespace Tests\Unit\Services\Sms;


use App\Services\Sms\Exceptions\SmsVerifyException;
use App\Models\Verify\SmsVerify;
use App\Services\Sms\SmsVerifyService;
use App\ValueObjects\Token;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SmsVerifyBuilder;

class SmsVerifyTest extends TestCase
{
    use SmsVerifyBuilder;
    use DatabaseTransactions;

    /** @test */
    public function success_get_code()
    {
        $len = 6;
        \Config::set('sms.verify.code_length', $len);

        $code = app(SmsVerifyService::class)->getCode();
        $this->assertTrue(is_numeric($code));
        $this->assertEquals($len, strlen($code));
    }

    /** @test */
    public function success_get_sms_code()
    {
        \Config::set('sms.verify.sms_token_expired', 'PT1H');

        $token = app(SmsVerifyService::class)->getSmsToken();

        $this->assertTrue($token instanceof Token);
        $this->assertNotEmpty($token->getValue());
        $this->assertNotEmpty($token->getExpires());
    }

    /** @test */
    public function success_get_and_check_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();

        $obj = app(SmsVerifyService::class)
            ->getAndCheckByActionToken($smsVerify->action_token->getValue());

        $this->assertTrue($obj instanceof SmsVerify);
    }

    /** @test */
    public function get_and_check_not_find_action_token()
    {
        $actionToken = 'not_valid_token';

        $this->expectException(\DomainException::class);

        app(SmsVerifyService::class)
            ->getAndCheckByActionToken($actionToken);
    }

    /** @test */
    public function get_and_check_expires_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();

        $this->expectException(SmsVerifyException::class);

        CarbonImmutable::setTestNow(Carbon::now()->addHour());

        app(SmsVerifyService::class)
            ->getAndCheckByActionToken($smsVerify->action_token->getValue());

    }
}

