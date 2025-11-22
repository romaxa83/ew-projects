<?php

namespace App\Dto\Users;

class UserSettingsDto
{
    private string $authorizationExpirationPeriod;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->authorizationExpirationPeriod = $args['authorization_expiration_period'];

        return $dto;
    }

    public function getAuthorizationExpirationPeriod(): string
    {
        return $this->authorizationExpirationPeriod;
    }
}
