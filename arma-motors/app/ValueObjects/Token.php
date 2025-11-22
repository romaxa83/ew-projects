<?php

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use Webmozart\Assert\Assert;

final class Token
{
    private $value;

    public function __construct(string $value, CarbonImmutable $expires)
    {
        Assert::uuid($value);

        $this->value = mb_strtolower($value);
        $this->expires = $expires;
    }

    public function validate(string $value, CarbonImmutable $date): void
    {
        if (!$this->isEqualTo($value)) {
            throw new \DomainException('Token is invalid.');
        }
        if ($this->isExpiredTo($date)) {
            throw new \DomainException('Token is expired.');
        }
    }

    public function isExpiredTo(CarbonImmutable $date): bool
    {
        return $this->expires <= $date;
    }

    public function isExpiredToNow(): bool
    {
        return $this->expires <= CarbonImmutable::now();
    }

    private function isEqualTo(string $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return CarbonImmutable
     */
    public function getExpires(): CarbonImmutable
    {
        return $this->expires;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }
}

