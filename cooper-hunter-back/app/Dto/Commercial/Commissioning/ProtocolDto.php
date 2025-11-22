<?php

namespace App\Dto\Commercial\Commissioning;

use App\Dto\SimpleTranslationDto;

class ProtocolDto
{
    public string $type;

    /** @var array<SimpleTranslationDto> */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->type = $args['type'];

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
}

