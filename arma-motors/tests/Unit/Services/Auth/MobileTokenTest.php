<?php

namespace Tests\Unit\Services\Auth;

use App\Exceptions\ErrorsCode;
use App\Services\Auth\Exception\MobileTokenException;
use App\Services\Auth\MobileToken;
use Tests\TestCase;

class MobileTokenTest extends TestCase
{
    /** @test */
    public function success()
    {
        $key = 'secret';
        $payload = [
            'pld' => [
                'deviceId' => 'some device id',
                'refreshToken' => 'some refresh token',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);
        $this->assertIsString($token);

        // декодируем
        $decode = MobileToken::decode($token, $key);

        $this->assertTrue($decode instanceof MobileToken);
        $this->assertEquals($decode->getDeviceId(), $payload['pld']['deviceId']);
        $this->assertEquals($decode->getRefreshToken(), $payload['pld']['refreshToken']);
        $this->assertTrue($decode->equalsDeviceId($payload['pld']['deviceId']));
    }

    /** @test */
    public function without_device_id()
    {
        $key = 'secret';

        $payload = [
            'pld' => [
                'refreshToken' => 'some refresh token',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);

        $this->expectException(MobileTokenException::class);
        $this->expectExceptionMessage(__('error.mobile_token.incorrect device id'));
        $this->expectExceptionCode(ErrorsCode::MOBILE_TOKEN_INCORRECT_DEVICE_ID);

        // декодируем
        MobileToken::decode($token, $key);
    }

    /** @test */
    public function without_refresh_token()
    {
        $key = 'secret';
        $payload = [
            'pld' => [
                'deviceId' => 'some device id',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);

        $this->expectException(MobileTokenException::class);
        $this->expectExceptionMessage(__('error.mobile_token.incorrect refresh token'));
        $this->expectExceptionCode(ErrorsCode::MOBILE_TOKEN_INCORRECT_REFRESH_TOKEN);

        // декодируем
        MobileToken::decode($token, $key);
    }

    /** @test */
    public function not_equals_device_id()
    {
        $key = 'secret';
        $payload = [
            'pld' => [
                'deviceId' => 'some device id',
                'refreshToken' => 'some refresh token',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);
        $this->assertIsString($token);

        // декодируем
        $decode = MobileToken::decode($token, $key);

        $this->assertFalse($decode->equalsDeviceId('another device id'));
    }

    /** @test */
    public function asset_device_id()
    {
        $key = 'secret';
        $payload = [
            'pld' => [
                'deviceId' => 'some device id',
                'refreshToken' => 'some refresh token',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);
        $this->assertIsString($token);

        // декодируем
        $decode = MobileToken::decode($token, $key);

        $this->expectException(MobileTokenException::class);
        $this->expectExceptionMessage(__('error.mobile_token.not equals device id'));
        $this->expectExceptionCode(ErrorsCode::MOBILE_TOKEN_NOT_EQUALS_DEVICE_ID);

        $decode->assetDeviceId('another device id');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function success_asset_device_id()
    {
        $key = 'secret';
        $payload = [
            'pld' => [
                'deviceId' => 'some device id',
                'refreshToken' => 'some refresh token',
            ]
        ];

        // кодируем
        $token = MobileToken::encode($payload, $key);

        // декодируем
        $decode = MobileToken::decode($token, $key);
        $decode->assetDeviceId($payload['pld']['deviceId']);
    }
}
