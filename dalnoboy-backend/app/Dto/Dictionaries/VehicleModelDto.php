<?php

namespace App\Dto\Dictionaries;

use GraphQL\Type\Definition\IDType;

class VehicleModelDto
{
    private IDType|int $vehicleMakeId;
    private bool $active;
    private string $title;
    private bool $isModerated;
    private bool $isOffline;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->vehicleMakeId = $args['vehicle_make_id'];
        $dto->active = $args['active'];
        $dto->title = $args['title'];
        $dto->isModerated = $args['is_moderated'];
        $dto->isOffline = $args['is_offline'];

        return $dto;
    }

    public function getVehicleMakeId(): int
    {
        return $this->vehicleMakeId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isModerated(): bool
    {
        return $this->isModerated;
    }

    public function isOffline(): bool
    {
        return $this->isOffline;
    }
}
