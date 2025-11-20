<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a QueueStatus action.
 */
class QueueParamsEvent extends EventMessage
{
    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getMax(): int
    {
        return intval($this->getKey('Max'));
    }

    public function getStrategy(): string
    {
        return $this->getKey('Strategy');
    }

    public function getCalls(): int
    {
        return intval($this->getKey('Calls'));
    }

    public function getHoldTime(): int
    {
        return intval($this->getKey('HoldTime'));
    }

    public function getCompleted(): int
    {
        return intval($this->getKey('Completed'));
    }

    public function getAbandoned(): int
    {
        return intval($this->getKey('Abandoned'));
    }

    public function getServiceLevel(): int
    {
        return intval($this->getKey('ServiceLevel'));
    }

    public function getServiceLevelPerf(): float
    {
        return $this->getKey('ServiceLevelPerf');
    }

    public function getWeight(): int
    {
        return intval($this->getKey('Weight'));
    }
}
