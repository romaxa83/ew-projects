<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a change in a queue member (pause/unpause).
 */
class QueueMemberPausedEvent extends EventMessage
{
    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getLocation(): ?string
    {
        return $this->getKey('Location');
    }

    public function getMemberName(): string
    {
        return $this->getKey('MemberName');
    }

    public function getPaused(): bool
    {
        return intval($this->getKey('Paused')) != 0;
    }
}
