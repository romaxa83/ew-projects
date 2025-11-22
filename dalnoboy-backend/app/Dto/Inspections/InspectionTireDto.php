<?php


namespace App\Dto\Inspections;

class InspectionTireDto
{
    private int $tireId;
    private int $schemaWheelId;
    private float $ogp;
    private float $pressure;
    private ?string $comment;
    private ?array $problems;
    private ?array $recommendations;
    private bool $noProblems;
    public array $photos = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->tireId = $args['tire_id'];
        $dto->schemaWheelId = $args['schema_wheel_id'];
        $dto->ogp = $args['ogp'];
        $dto->pressure = $args['pressure'];
        $dto->comment = $args['comment'] ?? null;
        $dto->problems = !empty($args['problems']) ? array_values(array_unique($args['problems'])) : null;

        if (!empty($args['recommendations'])) {
            $dto->recommendations = [];
            foreach ($args['recommendations'] as $recommendation) {
                $dto->recommendations[] = InspectionRecommendationDto::byArgs($recommendation);
            }
        } else {
            $dto->recommendations = null;
        }

        $dto->noProblems = empty($dto->problems);

        foreach (data_get($args, 'photos', []) as $item){
            $dto->photos[] = InspectionTirePhotoDto::byArgs($item);
        }
        return $dto;
    }

    /**
     * @return int
     */
    public function getTireId(): int
    {
        return $this->tireId;
    }

    /**
     * @return int
     */
    public function getSchemaWheelId(): int
    {
        return $this->schemaWheelId;
    }

    /**
     * @return float
     */
    public function getOgp(): float
    {
        return $this->ogp;
    }

    /**
     * @return float
     */
    public function getPressure(): float
    {
        return $this->pressure;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return bool
     */
    public function isNoProblems(): bool
    {
        return $this->noProblems;
    }

    /**
     * @return int[]|null
     */
    public function getProblems(): ?array
    {
        return $this->problems;
    }

    /**
     * @return InspectionRecommendationDto[]|null
     */
    public function getRecommendations(): ?array
    {
        return $this->recommendations;
    }
}
