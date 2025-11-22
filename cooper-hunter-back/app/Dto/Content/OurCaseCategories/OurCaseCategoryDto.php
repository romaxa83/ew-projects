<?php

namespace App\Dto\Content\OurCaseCategories;

class OurCaseCategoryDto
{
    private bool $active;
    private string $slug;

    /** @var array<OurCaseCategoryTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->active = $args['active'];
        $dto->slug = $args['slug'];

        foreach ($args['translations'] as $translation) {
            $dto->translations[] = OurCaseCategoryTranslationDto::byArgs($translation);
        }

        return $dto;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return OurCaseCategoryTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
