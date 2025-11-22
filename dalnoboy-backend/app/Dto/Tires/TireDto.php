<?php

namespace App\Dto\Tires;

use GraphQL\Type\Definition\IDType;

class TireDto
{
    private string $serialNumber;
    private int|IDType $specificationId;
    private int|IDType|null $relationshipTypeId;
    private bool $active;
    private bool $isModerated;
    private ?int $changeReasonId;
    private ?string $changeReasonDescription;
    private bool $isOffline;
    private ?float $ogp;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->serialNumber = $args['serial_number'];
        $dto->specificationId = $args['specification_id'];
        $dto->relationshipTypeId = $args['relationship_type_id'] ?? null;
        $dto->active = $args['active'];
        $dto->isModerated = $args['is_moderated'];
        $dto->isOffline = $args['is_offline'];
        $dto->changeReasonId = $args['change_reason_id'] ?? null;
        $dto->changeReasonDescription = $args['change_reason_description'] ?? null;
        $dto->ogp = $args['ogp'] ?? null;

        return $dto;
    }

    public function getChangeReasonId(): ?int
    {
        return $this->changeReasonId;
    }

    public function getChangeReasonDescription(): ?string
    {
        return $this->changeReasonDescription;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getSpecificationId(): int|IDType
    {
        return $this->specificationId;
    }

    public function getRelationshipTypeId(): int|IDType|null
    {
        return $this->relationshipTypeId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isModerated(): bool
    {
        return $this->isModerated;
    }

    public function isOffline(): bool
    {
        return $this->isOffline;
    }

    public function getOgp(): ?float
    {
        return $this->ogp;
    }
}
