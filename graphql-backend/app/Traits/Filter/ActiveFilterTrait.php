<?php

declare(strict_types=1);

namespace App\Traits\Filter;

trait ActiveFilterTrait
{
    public function active(bool $value): void
    {
        $this->where(self::TABLE . '.active', $value);
    }
}
