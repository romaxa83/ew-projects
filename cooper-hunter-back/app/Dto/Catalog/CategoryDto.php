<?php

namespace App\Dto\Catalog;

use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Categories\Category;
use App\Traits\AssertData;

class CategoryDto
{
    use AssertData;

    private ?string $guid;

    private bool $active;
    private bool $main;
    private null|int $parentId;
    private bool $enableSeer;

    private string $slug;

    private array $translations = [];

    public static function byArgs(array $args): self
    {
        static::assetField($args, 'translations');

        $self = new self();

        $self->guid = $args['guid'] ?? null;

        $self->active = $args['active'] ?? Category::DEFAULT_ACTIVE;
        $self->main = $args['main'] ?? false;
        $self->parentId = $args['parent_id'] ?? null;
        $self->enableSeer = $args['enable_seer'] ?? false;

        $self->slug = $args['slug'];

        foreach ($args['translations'] ?? [] as $translation) {
            $self->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getMain(): bool
    {
        return $this->main;
    }

    public function getParentId(): null|int
    {
        return $this->parentId;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getEnableSeer(): bool
    {
        return $this->enableSeer;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
