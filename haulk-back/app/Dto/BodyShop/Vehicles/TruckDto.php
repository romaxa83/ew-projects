<?php

namespace App\Dto\BodyShop\Vehicles;

use App\Models\Vehicles\Vehicle;

class TruckDto extends \App\Dto\Vehicles\TruckDto
{
    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->vehicleData = [
            'unit_number' => $data['unit_number'],
            'vin' => $data['vin'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'type' => $data['type'],
            'license_plate' => $data['license_plate'],
            'notes' => $data['notes'] ?? null,
            'customer_id' => $data['owner_id'] ?? null,
            'color' => $data['color'] ?? null,
            'gvwr' => $data['gvwr'] ?? null,
        ];

        $dto->tags = $data['tags'] ?? [];

        $dto->attachments = $data[Vehicle::ATTACHMENT_FIELD_NAME] ?? [];

        return $dto;
    }
}
