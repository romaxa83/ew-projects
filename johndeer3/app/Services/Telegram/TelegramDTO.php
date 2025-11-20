<?php

namespace App\Services\Telegram;

use Throwable;

class TelegramDTO
{
    public const INFO = 'info';
    public const ERROR = 'error';
    public const WARN = 'warn';

    private $type;
    private $message = null;
    private $username = null;
    private $locate = null;

    private $errorMessage = null;
    private $errorLocate = null;

    private function __construct()
    {}

    public static function asError(string $locate, Throwable $error, ?string $username): self
    {
        $self = new self();

        $self->type = self::ERROR;
        $self->username = $username;
        $self->errorMessage = $error->getMessage() . ' ('. $error->getCode() .')';
        $self->errorLocate = $error->getFile() . ' ('. $error->getLine() .')';
        $self->locate = $locate;

        return $self;
    }

    public static function asInfo(string $message, ?string $userName): self
    {
        $self = new self();

        $self->type = self::INFO;
        $self->username = $userName;
        $self->message = $message;

        return $self;
    }

    public static function asWarn(?string $message, ?string $userName, ?string $locate): self
    {
        $self = new self();

        $self->type = self::WARN;
        $self->username = $userName;
        $self->message = $message;
        $self->locate = $locate;

        return $self;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getLocate(): ?string
    {
        return $this->locate;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorLocate(): ?string
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

    public function isWarn(): bool
    {
        return $this->type === self::WARN;
    }
}
