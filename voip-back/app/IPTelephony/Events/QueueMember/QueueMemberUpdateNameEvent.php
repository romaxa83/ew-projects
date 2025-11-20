<?php

namespace App\IPTelephony\Events\QueueMember;

class QueueMemberUpdateNameEvent
{
    public function __construct(
        protected string $oldName,
        protected string $newName,
    )
    {}

    public function getOldName(): string
    {
        return $this->oldName;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }
}
