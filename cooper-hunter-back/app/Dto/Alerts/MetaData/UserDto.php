<?php

namespace App\Dto\Alerts\MetaData;

use App\Contracts\Alerts\MetaDataDto;

class UserDto implements MetaDataDto
{
    private bool $registration = false;

    public static function fromEvent(?array $event): UserDto
    {
        $dto = new self();

        if (empty($event)) {
            return $dto;
        }

        $dto->registration = data_get($event, 'registration', false);

        return $dto;
    }

    public function isRegistration(): bool
    {
        return $this->registration;
    }
}
