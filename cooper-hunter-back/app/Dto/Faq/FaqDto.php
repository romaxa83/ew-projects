<?php

namespace App\Dto\Faq;

class FaqDto
{
    private bool $active;

    /** @var array<FaqTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'] ?? true;

        foreach ($args['translations'] as $translation) {
            $self->translations[] = FaqTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return FaqTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
