<?php

namespace App\PAMI\Message\Event;

/**
 * Event triggered when a variable is set via agi or dialplan.
 */
class VarSetEvent extends EventMessage
{
    const CASE_ID = 'CASE_ID';
    const SERIAL_NUMBER = 'SERIALNUMBER';
    const DESTROY = 'DESTROY';

    public function getPrivilege(): string
    {
        return $this->getKey('Privilege');
    }

    public function getChannel(): string
    {
        return $this->getKey('Channel');
    }

    public function getVariableName(): string
    {
        return $this->getKey('Variable');
    }

    public function getValue(): string
    {
        return $this->getKey('Value');
    }

    public function getUniqueID(): string
    {
        return $this->getKey('UniqueID');
    }

    public function isSerialNumber(): bool
    {
        return $this->getVariableName() == self::SERIAL_NUMBER;
    }

    public function isCaseId(): bool
    {
        return $this->getVariableName() == self::CASE_ID;
    }

    public function isDestroy(): bool
    {
        return $this->getVariableName() == self::DESTROY;
    }
}
