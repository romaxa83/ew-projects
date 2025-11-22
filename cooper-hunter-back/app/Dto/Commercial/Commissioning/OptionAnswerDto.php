<?php

namespace App\Dto\Commercial\Commissioning;

use App\Dto\SimpleTranslationDto;

class OptionAnswerDto
{
    public ?string $questionId;

    /** @var array<SimpleTranslationDto> */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->questionId = $args['question_id'] ?? null;

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

