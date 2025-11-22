<?php

namespace App\Dto\Faq\Questions;

class AnswerQuestionDto
{
    private string $answer;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->answer = $args['answer'];

        return $dto;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }
}
