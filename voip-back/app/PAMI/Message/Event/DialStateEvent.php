<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a dial is executed.
 * @see https://wiki.asterisk.org/wiki/display/AST/Asterisk+16+ManagerEvent_DialState
 */
class DialStateEvent extends EventMessage
{
    public const DIAL_STATUS_RINGING = 'RINGING';
    public const DIAL_STATUS_PROCEEDING = 'PROCEEDING';
    public const DIAL_STATUS_PROGRESS = 'PROGRESS';

    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getSubEvent(): string
    {
        return $this->getKey('SubEvent');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
    }

    public function getDialStatus(): string
    {
        return $this->getKey('DialStatus');
    }

    public function getDestConnectedLineNum(): string
    {
        return $this->getKey('DestConnectedLineNum');
    }

    public function getConnectedLineNum(): string
    {
        return $this->getKey('ConnectedLineNum');
    }

    public function getConnectedLineName(): string
    {
        return $this->getKey('ConnectedLineName');
    }

    public function isRingingStatus(): bool
    {
        return $this->getDialStatus() == self::DIAL_STATUS_RINGING;
    }

    public function isProceedingStatus(): bool
    {
        return $this->getDialStatus() == self::DIAL_STATUS_PROCEEDING;
    }

    public function isProgressStatus(): bool
    {
        return $this->getDialStatus() == self::DIAL_STATUS_PROGRESS;
    }
}

