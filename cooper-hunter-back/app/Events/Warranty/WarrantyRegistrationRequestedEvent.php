<?php

namespace App\Events\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;

class WarrantyRegistrationRequestedEvent
{
    public function __construct(
        private array $serialNumbers,
        private WarrantyStatus $newStatus
    ) {
    }

    public function getSerialNumbers(): array
    {
        return $this->serialNumbers;
    }

    public function getNewStatus(): WarrantyStatus
    {
        return $this->newStatus;
    }
}
