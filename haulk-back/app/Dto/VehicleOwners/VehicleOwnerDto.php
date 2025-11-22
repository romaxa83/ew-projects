<?php

namespace App\Dto\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;

class VehicleOwnerDto
{
    private array $vehicleOwnerData;

    private ?array $tags;

    private ?array $attachments;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->vehicleOwnerData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'phone_extension' => $data['phone_extension'] ?? null,
            'phones' => $data['phones'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        $dto->attachments = $data[VehicleOwner::ATTACHMENT_FIELD_NAME] ?? null;

        $dto->tags = $data['tags'] ?? [];

        return $dto;
    }

    public function getVehicleOwnerData(): array
    {
        return $this->vehicleOwnerData;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getAttachments(): array
    {
        return $this->attachments ?? [];
    }
}
