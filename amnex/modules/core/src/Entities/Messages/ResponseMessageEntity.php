<?php

declare(strict_types=1);

namespace Wezom\Core\Entities\Messages;

use Wezom\Core\Enums\Messages\MessageTypeEnum;

class ResponseMessageEntity
{
    public function __construct(
        public string $message,
        public MessageTypeEnum|string $type = MessageTypeEnum::SUCCESS
    ) {
    }

    public static function jobAlreadyInProgress(): self
    {
        return self::warning(
            __('core::messages.response.job-already-in-progress')
        );
    }

    public static function warning(string $message): self
    {
        return new self($message, MessageTypeEnum::WARNING);
    }

    public static function jobAddedSuccessfully(): self
    {
        return self::success(
            __('core::messages.response.job-added-successfully')
        );
    }

    public static function success(string $message): self
    {
        return new self($message);
    }

    public static function fail(string $message): self
    {
        return new self($message, MessageTypeEnum::DANGER);
    }
}
