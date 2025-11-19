<?php

declare(strict_types=1);

namespace Wezom\Core\Exceptions;

use GraphQL\Error\ClientAware;

/**
 * Only errors implementing this interface (and returning true from `isClientSafe()`)
 * will be formatted with original error message.
 *
 * All other errors will be formatted with generic "Internal server error".
 */
interface AppClientAware extends ClientAware
{
    public function setClientSafe(bool $clientSafe): void;

    public function setShouldBeReported(bool $shouldBeReported): void;

    /**
     * Determines if message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool;

    /**
     * Determines when the exception should be logged.
     */
    public function isShouldBeReported(): bool;

    /**
     * Call if exception should be logged.
     */
    public function shouldBeReported(): static;

    public function shouldNotBeReported(): static;

    /**
     * Call if exception message is safe to be displayed to a client.
     */
    public function clientSafe(): static;

    public function getHttpCode(): int;

    public function setHttpCode(int $httpCode): static;
}
