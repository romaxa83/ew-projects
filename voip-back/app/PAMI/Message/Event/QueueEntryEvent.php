<?php

namespace App\PAMI\Message\Event;

class QueueEntryEvent extends EventMessage
{
    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getPosition(): int
    {
        return $this->getKey('Position');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
    }

    public function getUniqueid(): string
    {
        return $this->getKey('Uniqueid');
    }

    public function getCallerIDNum(): string
    {
        return $this->getKey('CallerIDNum');
    }

    public function getCallerIDName(): string
    {
        return $this->getKey('CallerIDName');
    }

    public function getConnectedLineNum(): string
    {
        return $this->getKey('ConnectedLineNum');
    }

    public function getConnectedLineName(): string
    {
        return $this->getKey('ConnectedLineName');
    }

    public function getWait(): int
    {
        return $this->getKey('Wait');
    }

    public function getPriority(): string
    {
        return $this->getKey('Priority');
    }
}
