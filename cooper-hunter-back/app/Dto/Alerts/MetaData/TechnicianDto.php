<?php

namespace App\Dto\Alerts\MetaData;

use App\Contracts\Alerts\MetaDataDto;

class TechnicianDto implements MetaDataDto
{

    private bool $moderation = false;

    private bool $emailVerification = false;

    private bool $registration = false;

    public static function fromEvent(?array $event): TechnicianDto
    {
        $dto = new self();

        if (empty($event)) {
            return $dto;
        }

        $dto->moderation = data_get($event, 'moderation', false);
        $dto->emailVerification = data_get($event, 'email_verification', false);
        $dto->registration = data_get($event, 'registration', false);

        return $dto;
    }

    public function isChangeModerationStatus(): bool
    {
        return $this->moderation;
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
