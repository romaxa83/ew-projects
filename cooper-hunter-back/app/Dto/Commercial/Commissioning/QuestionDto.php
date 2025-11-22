<?php

namespace App\Dto\Commercial\Commissioning;

use App\Dto\SimpleTranslationDto;
use App\Enums\Commercial\Commissioning\QuestionStatus;

class QuestionDto
{
    public string $answerType;
    public string $photoType;
    public QuestionStatus $status;
    public ?string $protocolId;

    /** @var array<SimpleTranslationDto> */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->answerType = $args['answer_type'];
        $self->photoType = $args['photo_type'];
        $self->status = QuestionStatus::fromValue($args['status']);
        $self->protocolId = $args['protocol_id'] ?? null;

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

