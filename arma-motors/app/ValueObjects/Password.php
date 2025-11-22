<?php

namespace App\ValueObjects;

final class Password
{
    public const DEFAULT = 'password';

    private string $value;

    public function __construct()
    {
        $this->value = config('admin.password.random')
            ? \Str::random(config('admin.password.length'))
            : self::DEFAULT;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
