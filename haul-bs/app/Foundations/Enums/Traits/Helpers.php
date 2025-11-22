<?php

declare(strict_types=1);

namespace App\Foundations\Enums\Traits;

trait Helpers
{
    public function toUpperCase(): string
    {
        return strtoupper($this->value);
    }
}
