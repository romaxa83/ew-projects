<?php

namespace App\Services\Telegram;

use Throwable;

class TelegramDTO
{
    public const INFO = 'info';
    public const ERROR = 'error';

    private string $type;
    private null|string $message = null;
    private null|string $username = null;
    private null|string $locate = null;

    private null|string $errorMessage = null;
    private null|string $errorLocate = null;

    private function __construct()
    {}

    public static function asError(string $locate, Throwable $error, null|string $username): self
    {
        $self = new self();

        $self->type = self::ERROR;
        $self->username = $username;
        $self->errorMessage = $error->getMessage() . ' ('. $error->getCode() .')';
        $self->errorLocate = $error->getFile() . ' ('. $error->getLine() .')';
        $self->locate = $locate;

        return $self;
    }

    public static function asInfo(string $message, null|string $userName): self
    {
        $self = new self();

        $self->type = self::INFO;
        $self->username = $userName;
        $self->message = $message;

        return $self;
    }

    public function getMessage(): null|string
    {
        return $this->message;
    }

    public function getUsername(): null|string
    {
        return $this->username;
    }

    public function getLocate(): null|string
    {
        return $this->locate;
    }

    public function getErrorMessage(): null|string
    {
        return $this->errorMessage;
    }

    public function getErrorLocate(): null|string
    {
        return $this->errorLocate;
    }

    public function isError(): bool
    {
        return $this->type === self::ERROR;
    }

    public function isInfo(): bool
    {
        return $this->type === self::INFO;
    }
}
