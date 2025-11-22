<?php

namespace App\Dto\Catalog\Labels;

use App\Dto\SimpleTranslationDto;
use App\Enums\Catalog\Labels\ColorType;

class LabelDto
{
    public ColorType $colorType;

    /** @var array<SimpleTranslationDto> */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->colorType = ColorType::fromValue($args['color_type']);

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
