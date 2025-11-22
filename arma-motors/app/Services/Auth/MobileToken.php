<?php

namespace App\Services\Auth;

use App\Services\Auth\Exception\MobileTokenException;
use Firebase\JWT\JWT;

class MobileToken
{
    private const ALGORITHM = ['HS256'];

    private string $deviceId;
    private string $refreshToken;

    private function __construct($data)
    {
        $payload = (array)$data->pld;

        if(!key_exists('refreshToken', $payload)){
            MobileTokenException::throwIncorrectRefreshToken();
        }

        if(!key_exists('deviceId', $payload)){
            MobileTokenException::throwIncorrectDeviceId();
        }

        $this->refreshToken = $payload['refreshToken'];
        $this->deviceId = $payload['deviceId'];
    }

    public static function encode(array $payload, string $key): string
    {
        return JWT::encode($payload, $key);
    }

    public static function decode(string $token, string $key): self
    {
        return new self(JWT::decode($token, $key, self::ALGORITHM));
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function equalsDeviceId(string $deviceId): bool
    {
        return $this->getDeviceId() === $deviceId;
    }

    public function assetDeviceId(string $deviceId): void
    {
        if(!$this->equalsDeviceId($deviceId)){
            MobileTokenException::throwNotEqualsDeviceId();
        }
    }
}

