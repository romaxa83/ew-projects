<?php

namespace App\ValueObjects;

use Webmozart\Assert\Assert;

final class Uuid
{
    private string $value;

    public function __construct(string $value)
    {
//        Assert::uuid($value);

        $this->value = mb_strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equalsTo(string $value): bool
    {
        return $this->value === $value;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function create(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }
}

