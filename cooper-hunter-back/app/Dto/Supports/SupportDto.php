<?php

namespace App\Dto\Supports;

use App\ValueObjects\Phone;

class SupportDto
{
    private Phone $phone;

    /** @var array<SupportTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->phone = new Phone($args['phone']);

        foreach ($args['translations'] as $translation) {
            $self->translations[] = SupportTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    /**
     * @return SupportTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
