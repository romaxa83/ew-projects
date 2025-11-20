<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for the end of the list when an action CoreShowChannels
 * is issued.
 */
class CoreShowChannelsCompleteEvent extends EventMessage
{
    public function getListItems(): string
    {
        return $this->getKey('ListItems');
    }
}
