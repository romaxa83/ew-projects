<?php

namespace App\Dto\Catalog;

use App\Dto\SimpleTranslationDto;
use App\Enums\Categories\CategoryTypeEnum;
use App\Models\Catalog\Categories\Category;
use App\Models\Localization\Language;
use Illuminate\Support\Str;

class CategoryImportDto
{
    private int $id;
    private ?int $parentId;
    private string $slug;
    private bool $active;
    private ?CategoryTypeEnum $type;

    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->id = $args[0];
        $self->parentId = $args[1] ?: null;
        $self->slug = Str::slug($args[2] . ' ' . $args[0]);
        $self->active = Category::DEFAULT_ACTIVE;
        $self->type = CategoryTypeEnum::getType($args[2]);

        languages()->each(
            fn(Language $language) => $self->translations[] = SimpleTranslationDto::byArgs(
                [
                    'title' => $args[2] . ($language->slug === 'en' ? '' : ' __(Translates into ' . $language->slug . ')'),
                    'language' => $language->slug,
                ]
            )
        );

        return $self;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getParentId(): null|int
    {
        return $this->parentId;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): ?CategoryTypeEnum
    {
        return $this->type;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}

