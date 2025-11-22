<?php

namespace Tests\Feature\Queries\Verify;

use App\Exceptions\ErrorsCode;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SmsVerifyBuilder;
use Webmozart\Assert\Assert;

class SmsCheckTest extends TestCase
{
    use DatabaseTransactions;
    use SmsVerifyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $phone = '+380957775544';
        $sms = $this->smsVerifyBuilder()->setPhone($phone)->create();

        $this->assertNull($sms->action_token);

        $response = $this->graphQL($this->getQueryStrCheck($sms->sms_token->getValue(), $sms->sms_code))
            ->assertOk();

        $responseData = $response->json('data.smsCheck');

        $this->assertArrayHasKey('actionToken', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);
        $this->assertNull(Assert::uuid($responseData['actionToken']));
        $this->assertNotEquals($responseData['actionToken'], $sms->sms_token->getValue());

        $sms->refresh();
        $this->assertNotNull($sms->action_token);

        $this->assertEquals($responseData['actionToken'], $sms->action_token->getValue());
    }

    /** @test */
    public function expire_sms_token()
    {
        $phone = '+380957775544';
        $sms = $this->smsVerifyBuilder()->setPhone($phone)->create();

        CarbonImmutable::setTestNow(Carbon::now()->addHour());

        $response = $this->graphQL($this->getQueryStrCheck($sms->sms_token->getValue(), $sms->sms_code));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'),  __('error.expired sms token'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::SMS_TOKEN_EXPIRED);

        $sms->refresh();
        $this->assertNull($sms->action_token);
    }

    /** @test */
    public function wrong_sms_code()
    {
        $phone = '+380957775544';
        $sms = $this->smsVerifyBuilder()->setPhone($phone)->create();

        $response = $this->graphQL($this->getQueryStrCheck($sms->sms_token->getValue(), '0000'));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'),  __('error.sms code not equals'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::SMS_CODE_WRONG);

        $sms->refresh();
        $this->assertNull($sms->action_token);
    }

    public function getQueryStrCheck($token, $code): string
    {
        return  sprintf('{
            smsCheck (smsCode: "%s", smsToken: "%s"){
                actionToken
                status
                message
              }
            }',
            $code,
            $token,
        );
    }
}
