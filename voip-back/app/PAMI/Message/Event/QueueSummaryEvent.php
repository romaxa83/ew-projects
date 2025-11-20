<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a QueueSummary action.
 */
class QueueSummaryEvent extends EventMessage
{
    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getLoggedIn(): string
    {
        return $this->getKey('LoggedIn');
    }

    public function getAvailable(): string
    {
        return $this->getKey('Available');
    }

    public function getCallers(): string
    {
        return $this->getKey('Callers');
    }

    public function getHoldTime(): int
    {
        return $this->getKey('HoldTime');
    }

    public function getLongestHoldTime(): int
    {
        return $this->getKey('LongestHoldTime');
    }
}
