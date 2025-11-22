<?php

namespace App\Dto\Vehicles;

use App\Enums\Vehicles\VehicleType;
use App\Models\Vehicles\Truck;

class VehicleDto
{
    public string $vin;
    public string $unitNumber;
    public string $make;
    public string $model;
    public string $year;
    public int|null $type;
    public string|null $licensePlate;
    public string|null $temporaryPlate;
    public string|null $notes;
    public string|null $color;
    public float|null $gvwr;
    public int|null $originId;
    public int|null $companyId;
    public string|int $customerId;

    public array $tags = [];
    public array $files = [];

    public static function byArgs(array $data): self
    {
        $self = new static();

        $self->vin = data_get($data, 'vin');
        $self->unitNumber = data_get($data, 'unit_number');
        $self->make = data_get($data, 'make');
        $self->model = data_get($data, 'model');
        $self->year = data_get($data, 'year');
        $self->type = data_get($data, 'type', VehicleType::VEHICLE_TYPE_OTHER);
        $self->licensePlate = data_get($data, 'license_plate');
        $self->notes = data_get($data, 'notes');
        $self->color = data_get($data, 'color');
        $self->gvwr = data_get($data, 'gvwr');
        $self->customerId = data_get($data, 'owner_id');

        $self->temporaryPlate = data_get($data, 'temporary_plate');
        $self->originId = data_get($data, 'origin_id');
        $self->companyId = data_get($data, 'company_id');

        $self->tags = data_get($data, 'tags', []);
        $self->files = data_get($data, Truck::ATTACHMENT_FIELD_NAME, []);

        return $self;
    }
}

