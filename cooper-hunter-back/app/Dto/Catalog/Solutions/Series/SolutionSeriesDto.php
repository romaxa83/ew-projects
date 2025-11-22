<?php

namespace App\Dto\Catalog\Solutions\Series;

use App\Dto\SimpleTranslationDto;

class SolutionSeriesDto
{
    private string $slug;

    /**
     * @var array<SimpleTranslationDto>
     */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->slug = $args['slug'];

        foreach ($args['translations'] ?? [] as $translation) {
            $dto->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $dto;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return SimpleTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
