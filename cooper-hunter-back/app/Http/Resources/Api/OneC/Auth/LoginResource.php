<?php

namespace App\Http\Resources\Api\OneC\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Passport\Passport;

class LoginResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource;

        return [
            'token_type' => $resource['token_type'],
            'access_expires_in' => $resource['expires_in'],
            'refresh_expires_in' => dateIntervalToSeconds(
                Passport::$refreshTokensExpireIn
            ),
            'access_token' => $resource['access_token'],
            'refresh_token' => $resource['refresh_token'],
        ];
    }
}
