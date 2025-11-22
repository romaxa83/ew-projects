<?php

namespace App\Services\Telegram;

class SendDataDto
{
    public const INFO = 'info';
    public const ERROR = 'error';

    public string $type;
    public ?string $msg = null;
    public ?string $username = null;
    public array $data = [];

    public static function make (array $data): self
    {
        $self = new self();

        $self->type = $data['type'] ?? self::INFO;
        $self->username = $data['username'] ?? null;
        $self->msg = $data['msg'] ?? null;
        $self->data = $data['data'] ?? [];

        return $self;
    }
}
