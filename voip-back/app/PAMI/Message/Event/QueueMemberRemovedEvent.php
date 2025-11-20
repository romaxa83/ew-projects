<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered for a QueueMemberRemove action.
 */
class QueueMemberRemovedEvent extends EventMessage
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
}
