<?php

namespace App\Dto\Technicians;

use App\Traits\Dto\CountryIDFromDB;
use App\Traits\Dto\WithUserProps;

class TechnicianDto
{
    use CountryIDFromDB;
    use WithUserProps;

    private int $stateId;
    private string $countryId;
    private string $license;

    public static function byArgs(array $args): static
    {
        $self = new static();

        $self->setUserProps($args);

        $self->stateId = $args['state_id'];
        $self->countryId = self::countryIdFromDB($args['country_code']);
        $self->license = $args['license'];

        return $self;
    }

    public function getStateId(): int
    {
        return $this->stateId;
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function getLicense(): string
    {
        return $this->license;
    }
}
