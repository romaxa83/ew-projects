<?php

namespace App\Dto\BodyShop\Vehicles;

use App\Models\Vehicles\Vehicle;

class TrailerDto extends \App\Dto\Vehicles\TrailerDto
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
            'license_plate' => $data['license_plate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'customer_id' => $data['owner_id'],
            'color' => $data['color'] ?? null,
            'gvwr' => $data['gvwr'] ?? null,
        ];

        $dto->tags = $data['tags'] ?? [];

        $dto->attachments = $data[Vehicle::ATTACHMENT_FIELD_NAME] ?? [];

        return $dto;
    }
}
