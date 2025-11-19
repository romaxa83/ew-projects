<?php

namespace Wezom\Core\Listeners;

use Laravel\Octane\Events\RequestReceived;
use Spatie\Permission\PermissionRegistrar;

class PermissionServiceListener
{
    public function handle(RequestReceived $event): void
    {
        $event->sandbox[PermissionRegistrar::class]->clearPermissionsCollection();
    }
}
