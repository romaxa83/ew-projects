<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a QueueStatus action.
 */
class QueueMemberEvent extends EventMessage
{
    const STATUS_AST_DEVICE_UNKNOWN     = 1;
    const STATUS_AST_DEVICE_NOT_INUSE   = 1;
    const STATUS_AST_DEVICE_INUSE       = 2; // агент разговаривает
    const STATUS_AST_DEVICE_BUSY        = 3;
    const STATUS_AST_DEVICE_INVALID     = 4;
    const STATUS_AST_DEVICE_UNAVAILABLE = 5;
    const STATUS_AST_DEVICE_RINGING     = 6;
    const STATUS_AST_DEVICE_RINGINUSE   = 7;
    const STATUS_AST_DEVICE_ONHOLD      = 8;

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
        return $this->getKey('Name');
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

    public function getInCall(): int
    {
        return $this->getKey('InCall');
    }

    public function getPaused(): bool
    {
        return intval($this->getKey('Paused')) != 0;
    }
}
