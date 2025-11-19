<?php

declare(strict_types=1);

namespace Wezom\Core\Entities\Auth;

use Wezom\Core\Models\Auth\PersonalSession;

class SanctumSession
{
    protected PersonalSession $session;
    protected NewAccessToken $accessToken;
    protected NewRefreshToken $refreshToken;

    /**
     * @param  PersonalSession  $session
     * @param  NewAccessToken  $accessToken
     * @param  NewRefreshToken  $refreshToken
     */
    public function __construct(PersonalSession $session, NewAccessToken $accessToken, NewRefreshToken $refreshToken)
    {
        $this->session = $session;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getSession(): PersonalSession
    {
        return $this->session;
    }

    public function getAccessToken(): NewAccessToken
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): NewRefreshToken
    {
        return $this->refreshToken;
    }

    public function getTokenType(): string
    {
        return 'Bearer';
    }
}
