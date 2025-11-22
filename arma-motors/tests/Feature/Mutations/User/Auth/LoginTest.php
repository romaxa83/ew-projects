<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Exceptions\ErrorsCode;
use App\Repositories\Passport\OAuthRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class LoginTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->phone, $builder->getPassword())])
            ->assertOk();

        $responseData = $response->json('data.userLogin');

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertArrayHasKey('expiresIn', $responseData);
        $this->assertArrayHasKey('tokenType', $responseData);
        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertArrayHasKey('salt', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('phone', $responseData['user']);
        $this->assertArrayHasKey('phoneVerified', $responseData['user']);
        $this->assertArrayHasKey('status', $responseData['user']);
        $this->assertEquals($responseData['user']['phone'], $user->phone);
        $this->assertTrue($responseData['user']['phoneVerified']);
        $this->assertEquals($responseData['user']['status'], $this->user_status_active);
        $this->assertEquals($responseData['user']['status'], $this->user_status_active);
        $this->assertIsString($responseData['salt']);

        $this->assertEquals($responseData['salt'], $user->salt);
    }

    /** @test */
    public function success_with_update_field()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();

        $data = [
            'phone' => $user->phone,
            'password' => $builder->getPassword(),
            'deviceId' => 'some_device_id',
            'fcmToken' => 'some_fcm_token',
        ];

        $this->assertNull($user->fcm_token);
        $this->assertNotNull($user->device_id);
        $this->assertNotEquals($user->device_id, $data['deviceId']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrWithAdditionalField($data)])
            ->assertOk();

        $responseData = $response->json('data.userLogin');

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertArrayHasKey('expiresIn', $responseData);
        $this->assertArrayHasKey('tokenType', $responseData);
        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('fcmToken', $responseData['user']);
        $this->assertArrayHasKey('deviceId', $responseData['user']);
        $this->assertEquals($responseData['user']['fcmToken'], $data['fcmToken']);
        $this->assertEquals($responseData['user']['deviceId'], $data['deviceId']);
        $this->assertEquals($responseData['user']['id'], $user->id);
    }

    /** @test */
    public function login_from_another_device_with_message()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $authRepository = resolve(OAuthRepository::class);

        $data = [
            'phone' => $user->phone,
            'password' => $builder->getPassword(),
            'deviceId' => $user->device_id,
            'dropCurrentSession' => 'false',
        ];

        $this->postGraphQL(['query' => $this->getQueryStrAnotherDevice($data)]);
        $firstAutRow = $authRepository->authUserRow($user->id)->id;

        $data['deviceId'] = 'another_device';
        $response = $this->postGraphQL(['query' => $this->getQueryStrAnotherDevice($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('message.user.active session to another device'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::LOGIN_HAS_ACTIVE_SESSION);

        $this->assertNotNull($authRepository->authUserRow($user->id));
        $secondAutRow = $authRepository->authUserRow($user->id)->id;
        $this->assertEquals($firstAutRow, $secondAutRow);
    }

    /** @test */
    public function login_from_another_device_delete_session()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $authRepository = resolve(OAuthRepository::class);

        $data = [
            'phone' => $user->phone,
            'password' => $builder->getPassword(),
            'deviceId' => $user->device_id,
            'dropCurrentSession' => 'true',
        ];

        $this->postGraphQL(['query' => $this->getQueryStrAnotherDevice($data)]);
        $firstAutRow = $authRepository->authUserRow($user->id)->id;

        $data['deviceId'] = 'another_device';
        $response = $this->postGraphQL(['query' => $this->getQueryStrAnotherDevice($data)]);

        $responseData = $response->json('data.userLogin');
        $this->assertEquals($responseData['user']['deviceId'], $data['deviceId']);

        $secondAutRow = $authRepository->authUserRow($user->id)->id;
        $this->assertNotEquals($firstAutRow, $secondAutRow);
    }

    /** @test */
    public function wrong_password()
    {
        $user = $this->userBuilder()->phoneVerify()->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->phone, 'wrong_password')]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.wrong_user_login_credentials'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::LOGIN_WRONG_CREDENTIALS);
    }

    /** @test */
    public function wrong_phone()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $builder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr('3800000000000', $builder->getPassword())]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.wrong_user_login_credentials'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::LOGIN_WRONG_CREDENTIALS);
    }

    /** @test */
    public function not_verify_phone()
    {
        $builder = $this->userBuilder();
        $user = $builder->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($user->phone, $builder->getPassword())]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not verify phone'));
        $this->assertEquals($response->json('errors.0.extensions.code'), ErrorsCode::LOGIN_PHONE_NOT_VERIFY);
    }

    public static function getQueryStr(string $phone, string $password): string
    {
        return sprintf('
            mutation {
                userLogin(input:{
                    phone: "%s",
                    password: "%s"
                }) {
                    accessToken
                    refreshToken
                    expiresIn
                    tokenType
                    salt
                    user {
                        phone
                        phoneVerified
                        status
                    }
                }
            }',
            $phone,
            $password
        );
    }

    public static function getQueryStrWithAdditionalField(array $data): string
    {
        return sprintf('
            mutation {
                userLogin(input:{
                    phone: "%s",
                    password: "%s",
                    deviceId: "%s",
                    fcmToken: "%s"
                }) {
                    accessToken
                    refreshToken
                    expiresIn
                    tokenType
                    user {
                        id
                        fcmToken
                        deviceId
                    }
                }
            }',
            $data['phone'],
            $data['password'],
            $data['deviceId'],
            $data['fcmToken']
        );
    }

    public static function getQueryStrAnotherDevice(array $data): string
    {
        return sprintf('
            mutation {
                userLogin(input:{
                    phone: "%s",
                    password: "%s",
                    deviceId: "%s",
                    dropCurrentSession: %s
                }) {
                    accessToken
                    refreshToken
                    expiresIn
                    tokenType
                    user {
                        id
                        deviceId
                    }
                }
            }',
            $data['phone'],
            $data['password'],
            $data['deviceId'],
            $data['dropCurrentSession'],
        );
    }
}


