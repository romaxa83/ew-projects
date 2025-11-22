<?php

namespace App\Events\Warranty;

use App\Models\Warranty\WarrantyRegistration;

class WarrantyRegistrationProcessedEvent
{
    public function __construct(
        private WarrantyRegistration $registration,
        private array $serialNumbers,
    ) {
    }

    public function getWarrantyRegistration(): WarrantyRegistration
    {
        return $this->registration;
    }

    public function getSerialNumbers(): array
    {
        return $this->serialNumbers;
    }
}
