<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a status change in a queue.
 */
class QueueMemberStatusEvent extends EventMessage
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

    public function getMembership(): string
    {
        return $this->getKey('Membership');
    }

    public function getPenalty(): int
    {
        return $this->getKey('Penalty');
    }

    public function getCallsTaken(): int
    {
        return $this->getKey('CallsTaken');
    }

    public function getStatus(): int
    {
        return $this->getKey('Status');
    }

    public function getPaused()
    {
        return $this->getKey('Paused');
    }
}
