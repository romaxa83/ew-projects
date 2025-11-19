<?php

declare(strict_types=1);

namespace Wezom\Core\Entities\Auth;

use Wezom\Core\Models\Auth\PersonalRefreshToken;

class NewRefreshToken
{
    /**
     * The refresh token instance.
     */
    public PersonalRefreshToken $refreshToken;

    /**
     * The plain text version of the token.
     */
    public string $plainTextToken;

    /**
     * @param  PersonalRefreshToken  $refreshToken
     * @param  string  $plainTextToken
     */
    public function __construct(PersonalRefreshToken $refreshToken, string $plainTextToken)
    {
        $this->refreshToken = $refreshToken;
        $this->plainTextToken = $plainTextToken;
    }

    public function getRefreshToken(): PersonalRefreshToken
    {
        return $this->refreshToken;
    }

    public function getPlainTextToken(): string
    {
        return $this->plainTextToken;
    }

    public function getTokenExpiresIn(): int
    {
        if ($this->refreshToken->expires_at->lte(now())) {
            return 0;
        }

        return $this->refreshToken->expires_at->getTimestamp() - now()->getTimestamp();
    }
}
