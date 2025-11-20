<?php

namespace App\PAMI\Listener;

use App\PAMI\Message\Event\EventMessage;

interface IEventListener
{
    public function handle(EventMessage $event): void;
}
