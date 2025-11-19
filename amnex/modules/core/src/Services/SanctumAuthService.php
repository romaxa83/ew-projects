<?php

namespace Wezom\Core\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Throwable;
use Wezom\Core\Entities\Auth\NewAccessToken;
use Wezom\Core\Entities\Auth\NewRefreshToken;
use Wezom\Core\Entities\Auth\SanctumSession;
use Wezom\Core\Models\Auth\BaseAuthenticatable;
use Wezom\Core\Models\Auth\PersonalAccessToken;
use Wezom\Core\Models\Auth\PersonalRefreshToken;
use Wezom\Core\Models\Auth\PersonalSession;

class SanctumAuthService
{
    public function issueToken(
        BaseAuthenticatable $authenticatable,
        string $name,
        int $accessTokenLifetime,
        int $refreshTokenLifetime
    ): SanctumSession {
        $acTokenExpiresAt = now()->addMinutes($accessTokenLifetime);
        $rfTokenExpiresAt = now()->addMinutes($refreshTokenLifetime);

        [$session, $accessToken, $refreshToken] = make_transaction(
            function () use ($authenticatable, $name, $acTokenExpiresAt, $rfTokenExpiresAt) {
                $session = $this->createSession($authenticatable);

                $accessToken = $this->createAccessToken($authenticatable, $session, $name, $acTokenExpiresAt);

                $refreshToken = $this->createRefreshToken($session, $accessToken->accessToken, $rfTokenExpiresAt);

                return [$session, $accessToken, $refreshToken];
            }
        );

        return new SanctumSession($session, $accessToken, $refreshToken);
    }

    /**
     * @throws Throwable
     */
    public function refreshToken(
        string $token,
        int $accessTokenLifetime,
        int $refreshTokenLifetime
    ): SanctumSession {
        $refreshToken = PersonalRefreshToken::findToken($token);

        if (!$refreshToken) {
            throw new AuthenticationException();
        }

        if ($refreshToken->isExpired()) {
            /** @var ?PersonalSession $session */
            $session = $refreshToken->session;

            if ($session) {
                // We are cleaning device fcm token to prevent push notifications to the device.
                $session->clearDeviceFcmToken();

                // Removing session with all related tokens. Tokens should be deleted by ON DELETE CASCADE.
                $session->delete();
            }

            throw new AuthenticationException();
        }

        $oldAccessToken = $refreshToken->accessToken;

        /** @var BaseAuthenticatable $authenticatable */
        $authenticatable = $oldAccessToken->tokenable;

        /** @var ?PersonalSession $session */
        $session = $oldAccessToken->session;

        if (!$session) {
            throw new AuthenticationException();
        }

        $acTokenExpiresAt = now()->addMinutes($accessTokenLifetime);
        $rfTokenExpiresAt = now()->addMinutes($refreshTokenLifetime);

        [$accessToken, $refreshToken] = make_transaction(
            function () use ($authenticatable, $oldAccessToken, $session, $acTokenExpiresAt, $rfTokenExpiresAt) {
                $accessToken = $this->createAccessToken(
                    $authenticatable,
                    $session,
                    $oldAccessToken->name,
                    $acTokenExpiresAt
                );

                $refreshToken = $this->createRefreshToken($session, $accessToken->accessToken, $rfTokenExpiresAt);

                $this->revokeToken($oldAccessToken);

                return [$accessToken, $refreshToken];
            }
        );

        return new SanctumSession($session, $accessToken, $refreshToken);
    }

    protected function createSession(
        BaseAuthenticatable $authenticatable,
    ): PersonalSession {
        $session = new PersonalSession();
        $session->sessionable_type = $authenticatable->getMorphClass();
        $session->sessionable_id = $authenticatable->getKey();
        $session->save();

        return $session;
    }

    protected function createAccessToken(
        BaseAuthenticatable $authenticatable,
        PersonalSession $session,
        string $name,
        Carbon $expiresAt
    ): NewAccessToken {
        $plainTextToken = $this->generateToken();

        $token = new PersonalAccessToken();
        $token->session_id = $session->getKey();
        $token->tokenable_type = $authenticatable->getMorphClass();
        $token->tokenable_id = $authenticatable->getKey();
        $token->name = $name;
        $token->token = $this->hashFromText($plainTextToken);
        $token->abilities = null;
        $token->expires_at = $expiresAt;
        $token->save();

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    protected function createRefreshToken(
        PersonalSession $session,
        PersonalAccessToken $accessToken,
        Carbon $expiresAt
    ): NewRefreshToken {
        $plainTextToken = $this->generateToken();

        $token = new PersonalRefreshToken();
        $token->session_id = $session->getKey();
        $token->tokenable_type = $accessToken->tokenable_type;
        $token->tokenable_id = $accessToken->tokenable_id;
        $token->access_token_id = $accessToken->getKey();
        $token->token = $this->hashFromText($plainTextToken);
        $token->expires_at = $expiresAt;
        $token->save();

        return new NewRefreshToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    /**
     * Removes a pair of access and refresh tokens. Refresh token should be deleted by ON DELETE CASCADE.
     */
    public function revokeToken(PersonalAccessToken $oldAccessToken): void
    {
        $oldAccessToken->delete();
    }

    public function revokeAllTokens(BaseAuthenticatable $authenticatable): void
    {
        $authenticatable->tokens()->delete();
    }

    public function logout(BaseAuthenticatable $authenticatable): void
    {
        /** @var PersonalAccessToken|null $accessToken */
        $accessToken = $authenticatable->currentAccessToken();

        if (!$accessToken) {
            return;
        }

        /** @var ?PersonalSession $session */
        $session = $accessToken->session;

        if ($session) {
            // We are cleaning device fcm token to prevent push notifications to the device.
            $session->clearDeviceFcmToken();

            // Removing session with all related tokens. Tokens should be deleted by ON DELETE CASCADE.
            $session->delete();
        } else {
            // If somebody created token without session, but you shouldn't do that.
            $this->revokeToken($accessToken);
        }
    }

    public function logoutAllSessions(BaseAuthenticatable $authenticatable): void
    {
        $sessions = $authenticatable->sessions()->get();

        foreach ($sessions as $session) {
            // We are cleaning device fcm token to prevent push notifications to the device.
            $session->clearDeviceFcmToken();

            // Removing session with all related tokens. Tokens should be deleted by ON DELETE CASCADE.
            $session->delete();
        }

        // If somebody created tokens without session, but you shouldn't do that.
        if ($authenticatable->tokens()->exists()) {
            $this->revokeAllTokens($authenticatable);
        }
    }

    protected function generateToken(): string
    {
        return sprintf(
            '%s%s%s',
            config('sanctum.token_prefix', ''),
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );
    }

    protected function hashFromText(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }
}
