<?php


namespace App\Dto\Inspections;


class InspectionRecommendationDto
{
    private int $recommendationId;
    private bool $isConfirmed;
    private ?int $newTireId;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->recommendationId = $args['recommendation_id'];
        $dto->isConfirmed = $args['is_confirmed'];
        $dto->newTireId = $args['new_tire_id'] ?? null;

        return $dto;
    }

    /**
     * @return int
     */
    public function getRecommendationId(): int
    {
        return $this->recommendationId;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    /**
     * @return int|null
     */
    public function getNewTireId(): ?int
    {
        return $this->newTireId;
    }
}
