<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a dial is executed.
 */
class DialEvent extends EventMessage
{
    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getSubEvent(): string
    {
        return $this->getKey('SubEvent');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
    }

    public function getDestination(): string
    {
        return $this->getKey('Destination');
    }

    public function getCallerIDNum(): string
    {
        return $this->getKey('CallerIDNum');
    }

    public function getCallerIDName(): string
    {
        return $this->getKey('CallerIDName');
    }

    public function getUniqueID(): string
    {
        return $this->getKey('UniqueID');
    }

    public function getDestUniqueID(): string
    {
        return $this->getKey('DestUniqueID');
    }

    public function getDialString(): string
    {
        return $this->getKey('DialString');
    }

    public function getDialStatus(): string
    {
        return $this->getKey('DialStatus');
    }
}
