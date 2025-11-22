<?php

namespace App\Dto\Stores\Stores;

class StoreDto
{
    private int $storeCategoryId;
    private bool $active;
    private string $link;

    /** @var array<StoreTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->storeCategoryId = $args['store_category_id'];
        $self->active = $args['active'];
        $self->link = $args['link'];

        foreach ($args['translations'] as $translation) {
            $self->translations[] = StoreTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getStoreCategoryId(): int
    {
        return $this->storeCategoryId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return StoreTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
