<?php

namespace App\Dto\SupportRequests;


class SupportRequestMessageDto
{
    private string $text;

    public static function byArgs(array $args): SupportRequestMessageDto
    {
        $dto = new self();

        $dto->text = $args['text'];

        return $dto;
    }

    public function getText(): string
    {
        return $this->text;
    }
}

