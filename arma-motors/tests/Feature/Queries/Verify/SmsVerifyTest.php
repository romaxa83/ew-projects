<?php

namespace Tests\Feature\Queries\Verify;

use App\Events\SmsVerify\SendSmsCode;
use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use App\Models\Verify\SmsVerify;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\User\Auth\LoginTest;
use Tests\TestCase;
use Tests\Traits\Builders\SmsVerifyBuilder;
use Tests\Traits\UserBuilder;
use Webmozart\Assert\Assert;

class SmsVerifyTest extends TestCase
{
    use DatabaseTransactions;
    use SmsVerifyBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_with_phone()
    {
        \Event::fake([SendSmsCode::class]);
        $phone = '+380957775544';

        $response = $this->graphQL($this->getQueryStrPhone($phone))
            ->assertOk();

        $responseData = $response->json('data.smsVerify');

        $this->assertArrayHasKey('smsToken', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('smsCode', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);
        $this->assertNotEmpty($responseData['smsCode']);
        $this->assertNull(Assert::uuid($responseData['smsToken']));

        \Event::assertDispatched(SendSmsCode::class);
    }

    /** @test */
    public function success_with_access_token()
    {
        $builder = $this->userBuilder()->phoneVerify()->setStatus(User::ACTIVE);
        $user = $builder->create();

        $strQueryLogin = LoginTest::getQueryStr($builder->getPhone(), $builder->getPassword());

        $responseLogin = $this->graphQL($strQueryLogin);
        $accessToken = $responseLogin->json('data.userLogin.accessToken');

        $response = $this->graphQL($this->getQueryStrAccessToken($accessToken));

        $responseData = $response->json('data.smsVerify');

        $this->assertArrayHasKey('smsToken', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('smsCode', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);
        $this->assertNotEmpty($responseData['smsCode']);
        $this->assertNull(Assert::uuid($responseData['smsToken']));
    }

    /** @test */
    public function with_not_valid_access_token()
    {
        $response = $this->graphQL($this->getQueryStrAccessToken('not_valid_access_token'));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.invalid access token'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::ACCESS_TOKEN_NOT_VALID);
    }

    /** @test */
    public function empty_field()
    {
        $response = $this->graphQL($this->getQueryStrEmpty());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.sms verify not have required field'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::SMS_VERIFY_NOT_FIELD);
    }

    /** @test */
    public function success_with_phone_without_sms_code_to_response()
    {
        $phone = '+380957775544';
        \Config::set('sms.enable_sender', true);

        $response = $this->graphQL($this->getQueryStrPhone($phone));

        $responseData = $response->json('data.smsVerify');

        $this->assertArrayHasKey('smsCode', $responseData);

        $this->assertEmpty($responseData['smsCode']);
    }

    /** @test */
    public function response_as_exist_active_action_token()
    {
        // при запросе на верификацию, есть запись и в ней активен actionToken
        $phone = '+380957775544';
        $this->smsVerifyBuilder()->setPhone($phone)->withActionToken()->create();

        $response = $this->graphQL($this->getQueryStrPhone($phone));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.active action token'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::ACTION_TOKEN_ACTIVE);
    }

    /** @test */
    public function response_as_exist_expire_action_token()
    {
        // при запросе на верификацию, есть запись и в ней не активен actionToken
        $phone = '380957775544';
        $sms = $this->smsVerifyBuilder()->setPhone($phone)->withActionToken()->create();

        CarbonImmutable::setTestNow(Carbon::now()->addDay());

        $response = $this->graphQL($this->getQueryStrPhone($phone))
            ->assertOk();

        $responseData = $response->json('data.smsVerify');

        $this->assertArrayHasKey('smsToken', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);

        $this->expectException(ModelNotFoundException::class);
        $sms->refresh();

        $newRecord = SmsVerify::where('phone', $phone)->first();
        $this->assertNotEmpty($newRecord);
        $this->assertEquals($newRecord->sms_token->getValue(), $responseData['smsToken']);
    }

    /** @test */
    public function response_as_exist_sms_action_token()
    {
        // при запросе на верификацию, есть запись и в ней нет actionToken, но активен smsToken
        $phone = '+380957775544';
        $this->smsVerifyBuilder()->setPhone($phone)->create();

        $response = $this->graphQL($this->getQueryStrPhone($phone));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('error.active sms token'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::SMS_TOKEN_ACTIVE);
    }

    /** @test */
    public function response_as_exist_expire_refresh_token()
    {
        // при запросе на верификацию, есть запись и в ней нет actionToken, и не активен smsToken
        $phone = '380957775544';
        $sms = $this->smsVerifyBuilder()->setPhone($phone)->create();

        CarbonImmutable::setTestNow(Carbon::now()->addDay());

        $response = $this->graphQL($this->getQueryStrPhone($phone))
            ->assertOk();

        $responseData = $response->json('data.smsVerify');

        $this->assertArrayHasKey('smsToken', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);

        $this->expectException(ModelNotFoundException::class);
        $sms->refresh();

        $newRecord = SmsVerify::where('phone', $phone)->first();
        $this->assertNotEmpty($newRecord);
        $this->assertEquals($newRecord->sms_token->getValue(), $responseData['smsToken']);
    }

    public function getQueryStrPhone($phone): string
    {
        return  sprintf('{
            smsVerify (phone: "%s"){
                smsToken
                status
                message
                smsCode
              }
            }',
            $phone
        );
    }

    public function getQueryStrAccessToken($token): string
    {
        return  sprintf('{
            smsVerify (accessToken: "%s"){
                smsToken
                status
                message
                smsCode
              }
            }',
            $token
        );
    }

    public function getQueryStrEmpty(): string
    {
        return  sprintf('{
            smsVerify {
                smsToken
                status
                message
                smsCode
              }
            }'
        );
    }
}



