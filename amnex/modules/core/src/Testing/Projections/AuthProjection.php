<?php

declare(strict_types=1);

namespace Wezom\Core\Testing\Projections;

class AuthProjection extends Projection
{
    protected function fields(): array
    {
        return [
            'tokenType',
            'accessToken',
            'accessExpiresIn',
            'refreshToken',
            'refreshExpiresIn',
        ];
    }
}
