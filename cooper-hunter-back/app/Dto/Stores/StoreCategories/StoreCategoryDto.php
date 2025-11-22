<?php

namespace App\Dto\Stores\StoreCategories;

class StoreCategoryDto
{
    private bool $active;

    /** @var array<StoreCategoryTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'];

        foreach ($args['translations'] as $translation) {
            $self->translations[] = StoreCategoryTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return StoreCategoryTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
