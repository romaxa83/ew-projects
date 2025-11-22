<?php

namespace App\Dto\SupportRequests;


class SupportRequestDto
{
    private int $subjectId;

    private SupportRequestMessageDto $message;

    public static function byArgs(array $args): SupportRequestDto
    {
        $dto = new self();

        $dto->subjectId = $args['subject_id'];

        $dto->message = SupportRequestMessageDto::byArgs($args['message']);

        return $dto;
    }

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    public function getMessage(): SupportRequestMessageDto
    {
        return $this->message;
    }
}

