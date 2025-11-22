<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class RegulationDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private bool $active;
    private int|null $days;
    private int|null $distance;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->days = $args['days'] ?? null;
        $dto->distance = $args['distance'] ?? null;
        $dto->setTranslations($args);

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }
}
