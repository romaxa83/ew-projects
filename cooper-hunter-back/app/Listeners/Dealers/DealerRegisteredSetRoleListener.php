<?php

namespace App\Listeners\Dealers;

use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\Events\Dealers\DealerRegisteredEvent;
use Core\Services\Permissions\RoleService;

class DealerRegisteredSetRoleListener
{
    public function __construct(private RoleService $service)
    {}

    public function handle(
        DealerRegisteredEvent|CreateOrUpdateDealerEvent $event
    ): void
    {
        $this->service->assignDealerDefaultRole($event->getDealer());
    }
}
