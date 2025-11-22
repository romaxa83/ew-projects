<?php

namespace App\Services\Auth;

use App\Exceptions\ErrorsCode;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Log;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class PassportService
{
    public function __construct(
        private AccessTokenController $tokenController
    ) {}

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
            Log::error($exception->getMessage());

            throw $exception;
        }
    }

    /**
     * @param array $parameters
     * @return array
     * @throws Throwable
     */
    protected function issueToken(array $parameters): array
    {
        try {
            $response = $this->tokenController->issueToken(
                resolve(ServerRequestInterface::class)->withParsedBody($parameters)
            );

            return $this->transform(json_to_array($response->getContent()));

        } catch (Throwable $exception) {
            Log::error($exception);
            throw $exception;
        }
    }

    private function transform(array $data): array
    {
        return $data;
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
                    'scope' => '',
                ]
            );
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function revokeTokens(int $userId, int $clientId): int
    {
        $refreshTokensRevoked = RefreshToken::query()
            ->join('oauth_access_tokens', 'oauth_access_tokens.id', '=', 'oauth_refresh_tokens.access_token_id')
            ->where('oauth_access_tokens.user_id', $userId)
            ->where('oauth_access_tokens.client_id', $clientId)
            ->where('oauth_access_tokens.revoked', false)
            ->delete();

        $tokensRevoked = Token::query()
            ->where('user_id', $userId)
            ->where('client_id', $clientId)
            ->where('revoked', false)
            ->delete();

        return $refreshTokensRevoked + $tokensRevoked;
    }

    public function getUserIdByAccessToken(string $accessToken)
    {
        $tokenParts = explode('.' ,$accessToken);

        if(!isset($tokenParts[1])){
            throw new \InvalidArgumentException(__('error.invalid access token'), ErrorsCode::ACCESS_TOKEN_NOT_VALID);
        }
        $parsToken = json_to_array(base64_decode($tokenParts[1]));

        if(!isset($parsToken['jti'])){
            throw new \InvalidArgumentException(__('error.invalid access token'), ErrorsCode::ACCESS_TOKEN_NOT_VALID);
        }
        $user_record = \DB::table('oauth_access_tokens')->where('id', $parsToken['jti'])->first();

        if($user_record){
            return $user_record->user_id;
        }

        throw new \DomainException(__('error.not found record by access token'), ErrorsCode::ACCESS_TOKEN_NOT_FOUND_RECORD);
    }
}

