<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Events\Firebase\FcmPush;
use App\Events\User\EmailConfirm;
use App\Events\User\NotUserFromAA;
use App\Exceptions\ErrorsCode;
use App\Listeners\Firebase\FcmPushListeners;
use App\Listeners\User\EmailConfirmListeners;
use App\Listeners\User\SendUserDataToAAListeners;
use App\Models\User\User;
use App\Models\Verify\SmsVerify;
use ErrorException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\SmsVerifyBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class SignUpTest extends TestCase
{
    use DatabaseTransactions;
    use Statuses;
    use SmsVerifyBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_but_not_user_in_aa()
    {
        \Event::fake([
            EmailConfirm::class,
            FcmPush::class,
            NotUserFromAA::class
        ]);

        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.signUp');
        $locale = \App::getLocale();

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertArrayHasKey('expiresIn', $responseData);
        $this->assertArrayHasKey('tokenType', $responseData);
        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertArrayHasKey('salt', $responseData);
        $this->assertArrayHasKey('id', $responseData['user']);
        $this->assertArrayHasKey('name', $responseData['user']);
        $this->assertArrayHasKey('email', $responseData['user']);
        $this->assertArrayHasKey('phone', $responseData['user']);
        $this->assertArrayHasKey('status', $responseData['user']);
        $this->assertArrayHasKey('emailVerified', $responseData['user']);
        $this->assertArrayHasKey('phoneVerified', $responseData['user']);
        $this->assertArrayHasKey('egrpoy', $responseData['user']);
        $this->assertArrayHasKey('deviceId', $responseData['user']);
        $this->assertArrayHasKey('fcmToken', $responseData['user']);
        $this->assertArrayHasKey('lang', $responseData['user']);
        $this->assertArrayHasKey('createdAt', $responseData['user']);
        $this->assertArrayHasKey('locale', $responseData['user']);
        $this->assertArrayHasKey('locale', $responseData['user']['locale']);
        $this->assertArrayHasKey('name', $responseData['user']['locale']);
        $this->assertArrayHasKey('newPhone', $responseData['user']);
        $this->assertArrayHasKey('newPhoneComment', $responseData['user']);

        $this->assertEquals($locale, $responseData['user']['lang']);
        $this->assertEquals($data['name'], $responseData['user']['name']);
        $this->assertEquals($data['email'], $responseData['user']['email']);
        $this->assertEquals($data['phone'], $responseData['user']['phone']);
        $this->assertFalse($responseData['user']['emailVerified']);
        $this->assertFalse($responseData['user']['phoneVerified']);
        $this->assertEquals($this->user_status_draft, $responseData['user']['status']);
        $this->assertNull($responseData['user']['deviceId']);
        $this->assertNull($responseData['user']['fcmToken']);
        $this->assertNull($responseData['user']['newPhone']);
        $this->assertNull($responseData['user']['newPhoneComment']);
        $this->assertIsString($responseData['salt']);

        // проверяет запустились ли события
        \Event::assertDispatched(EmailConfirm::class);
        \Event::assertDispatched(FcmPush::class);
        \Event::assertDispatched(NotUserFromAA::class);
        // проверяет какие обработчики обработали события
        \Event::assertListening(EmailConfirm::class, EmailConfirmListeners::class);
        \Event::assertListening(FcmPush::class, FcmPushListeners::class);
        \Event::assertListening(NotUserFromAA::class, SendUserDataToAAListeners::class);

        $user = User::find($responseData['user']['id']);

        $this->assertNotNull($user->salt);
        $this->assertEquals($user->salt, $responseData['salt']);

        $this->assertNotEmpty($user->emailVerifyObj);
    }

    /** @test */
    public function success_without_email()
    {
        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'password' => 'password',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithoutEmail($data)]);

        $responseData = $response->json('data.signUp');

        $this->assertArrayHasKey('id', $responseData['user']);
        $this->assertArrayHasKey('email', $responseData['user']);
        $this->assertArrayHasKey('status', $responseData['user']);
        $this->assertArrayHasKey('emailVerified', $responseData['user']);

        $this->assertFalse($responseData['user']['emailVerified']);
        $this->assertNull($responseData['user']['email']);

        $user = User::find($responseData['user']['id']);
        $this->assertEmpty($user->emailVerifyObj);
    }

    /** @test */
    public function success_with_action_token()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();

        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'password',
            'actionToken' => $smsVerify->action_token->getValue()
        ];

        $this->assertTrue(SmsVerify::where('action_token', $smsVerify->action_token->getValue())->exists());

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithActionToken($data)])
            ->assertOk();

        $responseData = $response->json('data.signUp');

        $this->assertArrayHasKey('phoneVerified', $responseData['user']);
        $this->assertTrue($responseData['user']['phoneVerified']);

        $this->assertFalse(SmsVerify::where('action_token', $smsVerify->action_token->getValue())->exists());
    }

    /** @test */
    public function success_with_all_field()
    {
        $smsVerify = $this->smsVerifyBuilder()->withActionToken()->create();

        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'password',
            'actionToken' => $smsVerify->action_token->getValue(),
            'egrpoy' => '1111111111',
            'deviceId' => 'some_device_id',
            'fcmToken' => 'some_fcm_token'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithAllField($data)]);

        $responseData = $response->json('data.signUp');

        $this->assertArrayHasKey('egrpoy', $responseData['user']);
        $this->assertArrayHasKey('deviceId', $responseData['user']);
        $this->assertArrayHasKey('fcmToken', $responseData['user']);

        $this->assertEquals($responseData['user']['egrpoy'], $data['egrpoy']);
        $this->assertEquals($responseData['user']['deviceId'], $data['deviceId']);
        $this->assertEquals($responseData['user']['fcmToken'], $data['fcmToken']);
    }

    /** @test */
    public function fail_exist_user_with_phone()
    {
        $phone = '380993344552';
        $this->userBuilder()->setPhone($phone)->create();

        $data = [
            'name' => 'tester',
            'phone' => $phone,
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('validation.unique', ['attribute' => __('validation.attributes.phone')]),
            $response->json('errors.0.message'));

        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::EDIT_PHONE_EXIST);
    }

    /** @test */
    public function fail_exist_user_archive_with_phone()
    {
        $phone = '380993344552';
        $this->userBuilder()->setPhone($phone)->softDeleted()->create();

        $data = [
            'name' => 'tester',
            'phone' => $phone,
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('validation.unique', ['attribute' => __('validation.attributes.phone')]),
            $response->json('errors.0.message'));

        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::EDIT_PHONE_EXIST);
    }

    /** @test */
    public function not_valid_action_token()
    {
        $this->smsVerifyBuilder()->withActionToken()->create();

        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'password',
            'actionToken' => 'not_valid_token'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithActionToken($data)])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function wrong_short_password()
    {
        $data = [
            'name' => 'tester',
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'short',
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function without_phone()
    {
        $data = [
            'name' => 'tester',
            'email' => 'test@test.com',
            'password' => 'password',
        ];

        $this->expectException(ErrorException::class);

        $this->postGraphQL(['query' => $this->getQueryStr($data)]);
    }

    /** @test */
    public function without_name()
    {
        $data = [
            'phone' => '380993344552',
            'email' => 'test@test.com',
            'password' => 'short',
        ];

        $this->expectException(ErrorException::class);

        $this->postGraphQL(['query' => $this->getQueryStr($data)]);
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                signUp(input:{
                    name: "%s",
                    phone: "%s",
                    email: "%s",
                    password: "%s",
                }) {
                    accessToken
                    refreshToken
                    expiresIn
                    tokenType
                    salt
                    user {
                        id
                        name
                        email
                        phone
                        status
                        emailVerified
                        phoneVerified
                        egrpoy
                        deviceId
                        fcmToken
                        lang
                        locale {
                            name
                            locale
                        }
                        createdAt
                        newPhone
                        newPhoneComment
                    }
                }
            }',
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['password'],
        );
    }

    private function getQueryStrWithoutEmail(array $data): string
    {
        return sprintf('
            mutation {
                signUp(input:{
                    name: "%s",
                    phone: "%s",
                    password: "%s",
                }) {
                    user {
                        id
                        email
                        status
                        emailVerified
                    }
                }
            }',
            $data['name'],
            $data['phone'],
            $data['password'],
        );
    }

    private function getQueryStrWithActionToken(array $data): string
    {
        return sprintf('
            mutation {
                signUp(input:{
                    name: "%s",
                    phone: "%s",
                    email: "%s",
                    password: "%s",
                    actionToken: "%s",
                }) {
                    user {
                        id
                        name
                        email
                        phone
                        status
                        emailVerified
                        phoneVerified
                        egrpoy
                        lang
                        locale {
                            name
                            locale
                        }
                        createdAt
                    }
                }
            }',
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['password'],
            $data['actionToken'],
        );
    }

    private function getQueryStrWithAllField(array $data): string
    {
        return sprintf('
            mutation {
                signUp(input:{
                    name: "%s",
                    phone: "%s",
                    email: "%s",
                    password: "%s",
                    actionToken: "%s",
                    deviceId: "%s",
                    fcmToken: "%s",
                    egrpoy: "%s",
                }) {
                    user {
                        id
                        status
                        phoneVerified
                        egrpoy
                        deviceId
                        fcmToken
                    }
                }
            }',
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['password'],
            $data['actionToken'],
            $data['deviceId'],
            $data['fcmToken'],
            $data['egrpoy'],
        );
    }
}

