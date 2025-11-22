<?php

namespace App\Dto\Commercial\Commissioning;

class AnswersDto
{
    /** @var array<AnswerDto> */
    private array $dtos = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        foreach ($args ?? [] as $answer) {
            $self->dtos[] = AnswerDto::byArgs($answer);
        }

        return $self;
    }

    public function getAnswerDtos(): array
    {
        return $this->dtos;
    }
}



