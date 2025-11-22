<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class RecommendationDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private array $problems;
    private array $regulations;
    private bool $active;


    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->problems = $args['problems'] ?? [];
        $dto->regulations = $args['regulations'] ?? [];
        $dto->active = $args['active'];
        $dto->setTranslations($args);

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getProblems(): array
    {
        return $this->problems;
    }

    public function getRegulations(): array
    {
        return $this->regulations;
    }
}
