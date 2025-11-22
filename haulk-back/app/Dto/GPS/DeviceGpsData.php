<?php

namespace App\Dto\GPS;

class DeviceGpsData
{
    public bool $movementStatus;
    public bool $positionValid;
    public int $speed;
    public int $serverTimestamp;

    public static function fromTelemetry(array $data): self
    {
        return self::byArgs([
            'movement_status' => $data['movement.status']['value'],
            'position_valid' => $data['position.valid']['value'],
            'speed' => $data['position']['value']['speed'],
            'server_timestamp' => $data['server.timestamp']['value'],
        ]);
    }

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->movementStatus = $data['movement_status'];
        $self->positionValid = $data['position_valid'];
        $self->speed = $data['speed'];
        $self->serverTimestamp = $data['server_timestamp'];

        return $self;
    }
}
