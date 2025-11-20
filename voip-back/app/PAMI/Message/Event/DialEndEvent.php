<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a dial action has completed.
 * @see https://wiki.asterisk.org/wiki/display/AST/Asterisk+13+ManagerEvent_DialEnd
 */
class DialEndEvent extends EventMessage
{
    public const DIAL_STATUS_ABORT = 'ABORT';
    public const DIAL_STATUS_ANSWER = 'ANSWER';
    public const DIAL_STATUS_BUSY = 'BUSY';
    public const DIAL_STATUS_CANCEL = 'CANCEL';
    public const DIAL_STATUS_CHANUNAVAIL = 'CHANUNAVAIL';
    public const DIAL_STATUS_CONGESTION = 'CONGESTION';
    public const DIAL_STATUS_CONTINUE = 'CONTINUE';
    public const DIAL_STATUS_GOTO = 'GOTO';
    public const DIAL_STATUS_NOANSWER = 'NOANSWER';

    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getChannel(): ?string
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

    public function getUniqueid(): string
    {
        return $this->getKey('Uniqueid');
    }

    public function getDestChannel(): string
    {
        return $this->getKey('DestChannel');
    }

    public function getDestChannelState(): string
    {
        return $this->getKey('DestChannelState');
    }

    public function getDestChannelStateDesc(): string
    {
        return $this->getKey('DestChannelStateDesc');
    }

    public function getDestCallerIDNum(): string
    {
        return $this->getKey('DestCallerIDNum');
    }

    public function getDestCallerIDName(): string
    {
        return $this->getKey('DestCallerIDName');
    }

    public function getDestConnectedLineNum(): string
    {
        return $this->getKey('DestConnectedLineNum');
    }

    public function getDestConnectedLineName(): string
    {
        return $this->getKey('DestConnectedLineName');
    }

    public function getDestAccountCode(): string
    {
        return $this->getKey('DestAccountCode');
    }

    public function getDestContext(): string
    {
        return $this->getKey('DestContext');
    }

    public function getDestExten(): string
    {
        return $this->getKey('DestExten');
    }

    public function getDestPriority(): string
    {
        return $this->getKey('DestPriority');
    }

    public function getDestUniqueid(): string
    {
        return $this->getKey('DestUniqueid');
    }

    public function getDialStatus(): string
    {
        return $this->getKey('DialStatus');
    }

    public function isStatusAnswer(): bool
    {
        return $this->getDialStatus() == self::DIAL_STATUS_ANSWER;
    }
}
