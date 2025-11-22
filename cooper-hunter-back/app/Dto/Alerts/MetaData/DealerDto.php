<?php

namespace App\Dto\Alerts\MetaData;

use App\Contracts\Alerts\MetaDataDto;

class DealerDto implements MetaDataDto
{
    private bool $emailVerification = false;

    private bool $registration = false;

    public static function fromEvent(?array $event): self
    {
        $dto = new self();

        if (empty($event)) {
            return $dto;
        }

        $dto->emailVerification = data_get($event, 'email_verification', false);
        $dto->registration = data_get($event, 'registration', false);

        return $dto;
    }

    public function isChangeEmailVerificationStatus(): bool
    {
        return $this->emailVerification;
    }

    public function isRegistration(): bool
    {
        return $this->registration;
    }
}
