<?php

namespace App\Services\Passport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Log;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class PassportService
{

    /**
     * @param string $username
     * @param string $password
     * @param int $clientId
     * @param string $clientSecret
     * @return array
     * @throws Throwable
     */
    public function auth(string $username, string $password, int $clientId, string $clientSecret): array
    {
        try {
            return $this->issueToken(
                [
                    'username' => $username,
                    'password' => $password,
                    'grant_type' => 'password',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]
            );
        } catch (Throwable $exception) {
            Log::error($exception);

            throw $exception;
        }
    }

    protected function issueToken(array $parameters): array
    {
        try {
            $response = resolve(AccessTokenController::class)
                ->issueToken(
                    resolve(ServerRequestInterface::class)->withParsedBody($parameters)
                );

            return $this->transform(json_to_array($response->getContent()));
        } catch (Throwable $exception) {
            Log::error($exception);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    protected function transform(array $token): array
    {
        $token['expires_at'] = CarbonImmutable::now()->addSeconds($token['expires_in']);

        return $token;
    }

    /**
     * @param string $refreshToken
     * @param int $clientId
     * @param string $clientSecret
     * @return array
     * @throws Throwable
     */
    public function refreshToken(string $refreshToken, int $clientId, string $clientSecret): array
    {
        try {
            return $this->issueToken(
                [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]
            );
        } catch (Throwable $exception) {
            Log::error($exception);

            throw $exception;
        }
    }

    public function revokeTokens(Authenticatable $authenticatable, int $clientId): int
    {
        $refreshTokensRevoked = $this->revokeRefreshTokens($authenticatable->getAuthIdentifier(), $clientId);

        $tokensRevoked = $this->revokeAccessTokens($authenticatable->getAuthIdentifier(), $clientId);

        return $refreshTokensRevoked + $tokensRevoked;
    }

    public function revokeRefreshTokens(int $userId, int $clientId, string $tokenId = null): int
    {
        $builder = RefreshToken::query()
            ->join('oauth_access_tokens', 'oauth_access_tokens.id', '=', 'oauth_refresh_tokens.access_token_id')
            ->where('oauth_access_tokens.user_id', $userId)
            ->where('oauth_access_tokens.client_id', $clientId)
            ->where('oauth_access_tokens.revoked', false);

        if ($tokenId) {
            $builder->where('oauth_access_tokens.id', '<>', $tokenId);
        }

        return $builder->update(['oauth_refresh_tokens.revoked' => true]);
    }

    public function revokeAccessTokens(int $userId, int $clientId, string $tokenId = null): int
    {
        $builder = Token::query()
            ->where('user_id', $userId)
            ->where('client_id', $clientId)
            ->where('revoked', false);

        if ($tokenId) {
            $builder->where('id', '<>', $tokenId);
        }

        return $builder->update(['revoked' => true]);
    }
}
