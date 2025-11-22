<?php

namespace App\Dto\Faq\Questions;

use App\ValueObjects\Email;

class AskAQuestionDto
{
    private string $name;
    private Email $email;
    private string $question;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->name = $args['name'];
        $dto->email = new Email($args['email']);
        $dto->question = $args['question'];

        return $dto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }
}
