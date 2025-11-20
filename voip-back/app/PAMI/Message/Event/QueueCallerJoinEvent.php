<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a caller joins a Queue.
 */
class QueueCallerJoinEvent extends EventMessage
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

    public function getCount(): string
    {
        return $this->getKey('Count');
    }

    public function getQueue(): string
    {
        return $this->getKey('Queue');
    }

    public function getPosition(): string
    {
        return $this->getKey('Position');
    }

    public function getUniqueID(): string
    {
        return $this->getKey('UniqueID');
    }

    public function getCallerIdNum(): string
    {
        return $this->getKey('CallerIdNum');
    }

    public function getCallerIdName(): string
    {
        return $this->getKey('CallerIdName');
    }

    public function getConnectedLineNum(): string
    {
        return $this->getKey('ConnectedLineNum');
    }

    public function getConnectedLineName(): string
    {
        return $this->getKey('ConnectedLineName');
    }

    public function getExten(): string
    {
        return $this->getKey('Exten');
    }

    public function getContext(): string
    {
        return $this->getKey('Context');
    }

    public function getAccountCode(): string
    {
        return $this->getKey('AccountCode');
    }

    public function getPriority(): string
    {
        return $this->getKey('Priority');
    }
}
