<?php

namespace Tests\Feature\Mutations\User\Auth;

use App\Exceptions\ErrorsCode;
use App\Services\Auth\MobileToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class RefreshTokenTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function refresh_token_success()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $user->refresh();

        // сначала логинимся, чтоб получить refreshToken
        $response = $this->graphQL(LoginTest::getQueryStr(
            $builder->getPhone(),
            $builder->getPassword()
        ))->assertOk();

        $responseData = $response->json('data.userLogin');

        // создаем mobileToken, который будем отправлять на рэфреш
        $payload = [
            'pld' => [
                'refreshToken' => $responseData['refreshToken'],
                'deviceId' => $user->device_id
            ]
        ];
        $mobileToken = MobileToken::encode($payload, $user->salt);

        $this->assertIsString($mobileToken);

        $responseRefresh = $this->graphQL($this->queryStrRefresh($user->id, $mobileToken));

        $responseRefreshData = $responseRefresh->json('data.userRefreshToken');

        $this->assertArrayHasKey('refreshToken', $responseRefreshData);
        $this->assertArrayHasKey('expiresIn', $responseRefreshData);
        $this->assertArrayHasKey('tokenType', $responseRefreshData);
        $this->assertArrayHasKey('accessToken', $responseRefreshData);

        $this->assertNotEquals($responseRefreshData['refreshToken'], $payload['pld']['refreshToken']);
        $this->assertNotEquals($responseRefreshData['accessToken'], $responseData['accessToken']);
    }

    /** @test */
    public function device_id_wrong()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $user->refresh();

        // сначала логинимся, чтоб получить refreshToken
        $response = $this->graphQL(LoginTest::getQueryStr(
            $builder->getPhone(),
            $builder->getPassword()
        ))->assertOk();

        $responseData = $response->json('data.userLogin');

        // создаем mobileToken, который будем отправлять на рэфреш
        $payload = [
            'pld' => [
                'refreshToken' => $responseData['refreshToken'],
                'deviceId' => 'wrong_device_id'
            ]
        ];
        $mobileToken = MobileToken::encode($payload, $user->salt);

        $this->assertIsString($mobileToken);

        $response = $this->graphQL($this->queryStrRefresh($user->id, $mobileToken));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.mobile_token.not equals device id'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::MOBILE_TOKEN_NOT_EQUALS_DEVICE_ID, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function refresh_token_wrong()
    {
        $builder = $this->userBuilder()->phoneVerify();
        $user = $builder->create();
        $user->refresh();

        // сначала логинимся, чтоб получить refreshToken
        $response = $this->graphQL(LoginTest::getQueryStr(
            $builder->getPhone(),
            $builder->getPassword()
        ))->assertOk();

        // создаем mobileToken, который будем отправлять на рэфреш
        $payload = [
            'pld' => [
                'refreshToken' => 'some_fake_refresh',
                'deviceId' => $user->device_id
            ]
        ];
        $mobileToken = MobileToken::encode($payload, $user->salt);

        $responseRefresh = $this->graphQL($this->queryStrRefresh($user->id, $mobileToken));

        $this->assertArrayHasKey('errors', $responseRefresh->json());

        $this->assertEquals($responseRefresh['errors'][0]['message'], 'The refresh token is invalid.');
        $this->assertEquals(ErrorsCode::MOBILE_TOKEN_PROBLEM_WITH_GENERATE_REFRESH_TOKEN, $responseRefresh->json('errors.0.extensions.code'));
    }

    public static function queryStrRefresh($userId,string $mobileToken)
    {
        return sprintf('
            mutation {
                userRefreshToken(input:{
                    id: "%s"
                    mobileToken:"%s"
                }) {
                    refreshToken
                    expiresIn
                    tokenType
                    accessToken
              }
            }',
            $userId,
            $mobileToken
        );
    }


}

