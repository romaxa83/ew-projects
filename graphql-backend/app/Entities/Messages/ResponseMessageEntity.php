<?php

namespace App\Entities\Messages;

use Core\Enums\Messages\MessageTypeEnum;

class ResponseMessageEntity
{
    public function __construct(
        public string $message,
        public string $type = MessageTypeEnum::SUCCESS
    ) {
    }

    public static function jobAlreadyInProgress(): static
    {
        return self::warning(
            __('messages.response.job-already-in-progress')
        );
    }

    public static function warning(string $message): static
    {
        return new static($message, MessageTypeEnum::WARNING);
    }

    public static function jobAddedSuccessfully(): static
    {
        return self::success(
            __('messages.response.job-added-successfully')
        );
    }

    public static function success(string $message): static
    {
        return new static($message);
    }

    public static function jobAddedFail(): static
    {
        return self::fail(
            __('messages.response.job-added-fail')
        );
    }

    public static function fail(string $message): static
    {
        return new static($message, MessageTypeEnum::DANGER);
    }
}
