<?php

namespace App\Dto\Catalog;

use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Features\Feature;
use App\Traits\AssertData;

class FeatureDto
{
    use AssertData;

    private ?string $guid;
    private bool $active;
    private bool $displayInMobile;
    private bool $displayInWeb;
    private bool $displayInFilter;

    /**
     * @var array<SimpleTranslationDTO>
     */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->guid = $args['guid'] ?? null;
        $self->active = $args['active'] ?? Feature::DEFAULT_ACTIVE;
        $self->displayInMobile = $args['display_in_mobile'] ?? false;
        $self->displayInWeb = $args['display_in_web'] ?? false;
        $self->displayInFilter = $args['display_in_filter'] ?? false;

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

    public function getDisplayInMobile(): bool
    {
        return $this->displayInMobile;
    }

    public function getDisplayInWeb(): bool
    {
        return $this->displayInWeb;
    }

    /**
     * @return bool
     */
    public function getDisplayInFilter(): bool
    {
        return $this->displayInFilter;
    }

    /**
     * @return SimpleTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}

