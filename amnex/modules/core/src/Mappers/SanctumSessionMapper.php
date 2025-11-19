<?php

namespace Wezom\Core\Mappers;

use Wezom\Core\Entities\Auth\SanctumSession;

class SanctumSessionMapper
{
    public function mapToType(SanctumSession $session): array
    {
        return [
            'tokenType' => $session->getTokenType(),
            'accessToken' => $session->getAccessToken()->getPlainTextToken(),
            'accessExpiresIn' => $session->getAccessToken()->getTokenExpiresIn(),
            'refreshToken' => $session->getRefreshToken()->getPlainTextToken(),
            'refreshExpiresIn' => $session->getRefreshToken()->getTokenExpiresIn(),
        ];
    }
}
