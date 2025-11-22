<?php

namespace App\Dto\Commercial\Commissioning;

class AnswerDto
{
    public string $projectProtocolQuestionID;
    public ?string $text;
    public array $media = [];
    public array $optionAnswerIds = [];
    public bool $sendOptionAnswer = false;
    public bool $sendMedia = false;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->projectProtocolQuestionID = $args['project_protocol_question_id'];
        $self->text = $args['text'] ?? null;
        $self->optionAnswerIds = $args['option_answer_ids'] ?? [];
        $self->media = $args['media'] ?? [];

        if (isset($args['option_answer_ids'])) {
            $self->sendOptionAnswer = true;
        }
        if (isset($args['media'])) {
            $self->sendMedia = true;
        }

        return $self;
    }
}



