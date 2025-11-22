<?php


namespace App\Dto\Alerts;


class AlertSendDto
{
    /**@var AlertRecipientDto[] $recipients */
    private array $recipients;
    private string $title;
    private string $description;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        foreach ($args['recipients'] as $recipient) {
            $dto->recipients[] = AlertRecipientDto::byArgs($recipient);
        }

        $dto->title = $args['title'];
        $dto->description = $args['description'];

        return $dto;
    }

    /**
     * @return AlertRecipientDto[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
