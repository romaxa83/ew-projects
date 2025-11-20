<?php

namespace App\PAMI\Message\Event;

/**
 * Triggered when a hangup is detected.
 */
class HangupEvent extends EventMessage
{
    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
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

    public function getCause(): string
    {
        return $this->getKey('Cause');
    }

    public function getCauseText(): string
    {
        return $this->getKey('Cause-txt');
    }
}
