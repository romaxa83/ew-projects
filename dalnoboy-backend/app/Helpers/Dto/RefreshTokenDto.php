<?php


namespace App\Helpers\Dto;


use Carbon\Carbon;

class RefreshTokenDto
{
    public string $id;
    public string $accessTokenId;
    public string $clientId;
    public string $userId;
    public Carbon $accessTokenExpiresAt;
    public array $scopes;
    public ?bool $revoked;
    public ?Carbon $expiresAt = null;

    public static function init(array $data): self
    {
        $refreshToken = new self();

        $refreshToken->id = $data['refresh_token_id'];
        $refreshToken->accessTokenId = $data['access_token_id'];
        $refreshToken->clientId = $data['client_id'];
        $refreshToken->userId = $data['user_id'];
        $refreshToken->accessTokenExpiresAt = Carbon::createFromTimestamp($data['expire_time']);
        $refreshToken->scopes = $data['scopes'];
        $refreshToken->revoked = data_get($data, 'revoked');
        if (!empty($data['expires_at'])) {
            $refreshToken->expiresAt = Carbon::parse($data['expires_at']);
        }

        return $refreshToken;
    }
}
