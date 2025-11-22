<?php

namespace App\Dto\Catalog\Manuals;

use App\Dto\SimpleTranslationDto;

class ManualGroupDto
{
    /**
     * @var array<SimpleTranslationDto>
     */
    private array $translations = [];
    private bool $showCommercialCertified = false;

    public static function byArgs(array $args): self
    {
        $self = new self();
        $self->showCommercialCertified = $args['show_commercial_certified'] ?? false;

        foreach ($args['translations'] ?? [] as $translation) {
            $self->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $self;
    }

    /**
     * @return SimpleTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getShowCommercialCertified(): bool
    {
        return $this->showCommercialCertified;
    }
}
