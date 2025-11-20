<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a QueueMemberAdd action.
 */
class QueueMemberAddedEvent extends EventMessage
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

    public function getCallsTaken()
    {
        return $this->getKey('CallsTaken');
    }

    public function getLastCall(): int
    {
        return $this->getKey('LastCall');
    }

    public function getStatus(): int
    {
        return $this->getKey('Status');
    }

    public function getPaused(): bool
    {
        return intval($this->getKey('Paused')) != 0;
    }
}
