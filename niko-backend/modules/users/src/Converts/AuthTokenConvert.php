<?php

namespace WezomCms\Users\Converts;

use WezomCms\Core\UseCase\DateFormatter;

class AuthTokenConvert
{
    public static function toFront(array $data)
    {
        if(isset($data['error'])){
            return [
                "error" => "invalid_request",
                "errorDescription" => "The refresh token is invalid.",
                "hint" => "Token has been revoked",
                "message" => "The refresh token is invalid."
            ];
        }

        return [
            'tokenType' => $data['token_type'] ?? null,
            'accessToken' => $data['access_token'] ?? null,
            'refreshToken' => $data['refresh_token'] ?? null,
            'expiresIn' => isset($data['expires_in']) ? DateFormatter::convertTimestampForFront($data['expires_in']) : null
        ];
    }
}
