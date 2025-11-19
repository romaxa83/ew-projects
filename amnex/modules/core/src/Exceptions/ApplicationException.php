<?php

declare(strict_types=1);

namespace Wezom\Core\Exceptions;

use RuntimeException;

/**
 * Only errors extending this class (and returning true from `isClientSafe()`)
 * will be formatted with original error message.
 *
 * All other errors will be formatted with generic "Internal server error".
 */
class ApplicationException extends RuntimeException implements AppClientAware
{
    protected int $httpCode = 200; // 200 for exceptions that doesn't match any http code (only for REST, not for graphql)
    protected bool $clientSafe = false;
    protected bool $shouldBeReported = true;

    public function setClientSafe(bool $clientSafe): void
    {
        $this->clientSafe = $clientSafe;
    }

    public function setShouldBeReported(bool $shouldBeReported): void
    {
        $this->shouldBeReported = $shouldBeReported;
    }

    /**
     * Determines if message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return $this->clientSafe;
    }

    /**
     * Determines when the exception should be logged.
     */
    public function isShouldBeReported(): bool
    {
        return $this->shouldBeReported;
    }

    /**
     * Call if exception should be logged.
     */
    public function shouldBeReported(): static
    {
        $this->setShouldBeReported(true);

        return $this;
    }

    public function shouldNotBeReported(): static
    {
        $this->setShouldBeReported(false);

        return $this;
    }

    /**
     * Call if exception message is safe to be displayed to a client.
     */
    public function clientSafe(): static
    {
        $this->setClientSafe(true);

        return $this;
    }

    public function getCategory(): string
    {
        return 'internal';
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function setHttpCode(int $httpCode): static
    {
        $this->httpCode = $httpCode;

        return $this;
    }
}
