<?php

namespace App\Listeners\Users;

use App\Events\Users\UserRegisteredEvent;
use Core\Services\Permissions\RoleService;

class UserRegisteredSetRoleListener
{
    public function __construct(private RoleService $service)
    {
    }

    public function handle(UserRegisteredEvent $event): void
    {
        $this->service->assignDefaultRole($event->getUser());
    }
}
