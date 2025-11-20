<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a caller abandons the queue.
 */
class QueueCallerAbandonEvent extends EventMessage
{
    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
    }

    public function getChannelState(): string
    {
        return $this->getKey('ChannelState');
    }

    public function getChannelStateDesc(): string
    {
        return $this->getKey('ChannelStateDesc');
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

    public function getAccountCode(): string
    {
        return $this->getKey('AccountCode');
    }

    public function getContext(): string
    {
        return $this->getKey('Context');
    }

    public function getExten(): string
    {
        return $this->getKey('Exten');
    }

    public function getPriority(): string
    {
        return $this->getKey('Priority');
    }

    public function getUniqueID(): string
    {
        return $this->getKey('UniqueID');
    }

    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getPosition(): string
    {
        return $this->getKey('Position');
    }

    public function getOriginalPosition(): string
    {
        return $this->getKey('OriginalPosition');
    }

    public function getHoldTime(): string
    {
        return $this->getKey('HoldTime');
    }
}
