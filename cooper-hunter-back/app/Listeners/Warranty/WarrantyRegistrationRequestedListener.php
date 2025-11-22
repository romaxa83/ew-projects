<?php

namespace App\Listeners\Warranty;

use App\Events\Warranty\WarrantyRegistrationRequestedEvent;
use App\Services\Warranty\WarrantyService;
use Illuminate\Contracts\Queue\ShouldQueue;

class WarrantyRegistrationRequestedListener implements ShouldQueue
{
    public function handle(WarrantyRegistrationRequestedEvent $event): void
    {
        app(WarrantyService::class)
            ->resolveSystemWarrantyStatusBySerials(
                $event->getSerialNumbers(),
                $event->getNewStatus(),
            );
    }
}
