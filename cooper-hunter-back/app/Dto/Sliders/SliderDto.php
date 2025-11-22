<?php

namespace App\Dto\Sliders;

class SliderDto
{
    private bool $active;
    private ?string $link;

    /** @var array<SliderTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'];
        $self->link = $args['link'] ?? null;

        foreach ($args['translations'] as $translation) {
            $self->translations[] = SliderTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @return SliderTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
