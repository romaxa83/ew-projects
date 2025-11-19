<?php

declare(strict_types=1);

namespace Wezom\Core\Entities\Auth;

use Wezom\Core\Models\Auth\PersonalAccessToken;

class NewAccessToken
{
    /**
     * The access token instance.
     */
    public PersonalAccessToken $accessToken;

    /**
     * The plain text version of the token.
     */
    public string $plainTextToken;

    /**
     * @param  PersonalAccessToken  $accessToken
     * @param  string  $plainTextToken
     */
    public function __construct(PersonalAccessToken $accessToken, string $plainTextToken)
    {
        $this->accessToken = $accessToken;
        $this->plainTextToken = $plainTextToken;
    }

    public function getAccessToken(): PersonalAccessToken
    {
        return $this->accessToken;
    }

    public function getPlainTextToken(): string
    {
        return $this->plainTextToken;
    }

    public function getTokenExpiresIn(): int
    {
        if ($this->accessToken->expires_at->lte(now())) {
            return 0;
        }

        return $this->accessToken->expires_at->getTimestamp() - now()->getTimestamp();
    }
}
