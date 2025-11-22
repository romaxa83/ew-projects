<?php

namespace App\Listeners\Technicians;

use App\Events\Technicians\TechnicianRegisteredEvent;
use Core\Services\Permissions\RoleService;

class TechnicianRegisteredSetRoleListener
{
    public function __construct(private RoleService $service)
    {}

    public function handle(TechnicianRegisteredEvent $event): void
    {
        $this->service->assignTechnicianDefaultRole($event->getTechnician());
    }
}
