<?php

namespace App\Entities\Saas\GPS;

use App\Models\GPS\Alert;
use App\Models\Vehicles\Truck;
use Illuminate\Database\Eloquent\Collection;

class TrackingEntity
{
    public int $id;
    public ?float $speed;
    public ?float $longitude;
    public ?float $latitude;
    public ?string $type;
    public ?int $typeDuration; //sec
    public bool $isTruck;
    public string $unitNumber;
    public ?string $driverName;
    public ?int $lastDrivingAt;
    public ?int $batteryLevel;
    public ?int $receivedAt;
    public ?self $trailer = null;

    public Collection $alerts;

    public function __construct($model)
    {
        $this->id = $model->id;
        $this->speed = isset($model->lastGPSHistory->speed)
            ? (float)$model->lastGPSHistory->speed
            : null
        ;
        $this->longitude = isset($model->lastGPSHistory->longitude)
            ? (float)$model->lastGPSHistory->longitude
            : null;
        $this->latitude = isset($model->lastGPSHistory->latitude)
            ? (float)$model->lastGPSHistory->latitude
            : null;
        $this->type = isset($model->lastGPSHistory->event_type)
            ? $model->lastGPSHistory->event_type
            : null;
        $this->typeDuration = isset($model->lastGPSHistory->event_duration)
            ? $model->lastGPSHistory->event_duration
            : null;
        $this->isTruck = $model instanceof Truck;
        $this->unitNumber = $model->unit_number;
        $this->driverName = isset($model->driver->full_name)
            ? $model->driver->full_name
            : null;
        $this->lastDrivingAt = $model->last_driving_at->timestamp ?? null;

        $this->batteryLevel = isset($model->lastGPSHistory->device_battery_level)
            ? $model->lastGPSHistory->device_battery_level
            : null;

        $this->receivedAt = isset($model->lastGPSHistory->received_at)
            ? $model->lastGPSHistory->received_at->timestamp
            : null;

        if(
            $this->isTruck
            && isset($model->driver->trailer)
        ){
            $this->trailer = new self($model->driver->trailer);
        }

        $this->alerts = isset($model->lastGPSHistory)
            ? $model->lastGPSHistory->alerts
            : new Collection();
    }
}

