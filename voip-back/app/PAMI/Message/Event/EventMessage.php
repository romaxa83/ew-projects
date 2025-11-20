<?php

namespace App\PAMI\Message\Event;

use App\PAMI\Message\IncomingMessage;

/**
 * This is a generic event received from ami.
 */
abstract class EventMessage extends IncomingMessage
{
    public function getName(): string
    {
        return $this->getKey('Event');
    }
}
