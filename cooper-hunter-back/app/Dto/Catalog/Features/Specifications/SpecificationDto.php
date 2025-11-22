<?php

namespace App\Dto\Catalog\Features\Specifications;

class SpecificationDto
{
    private bool $active;
    private string $icon;

    /** @var array<SpecificationTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'];
        $self->icon = $args['icon'];

        foreach ($args['translations'] as $t) {
            $self->translations[] = SpecificationTranslationDto::byArgs($t);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return SpecificationTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
