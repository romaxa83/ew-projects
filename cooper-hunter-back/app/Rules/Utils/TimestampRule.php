<?php

namespace App\Rules\Utils;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class TimestampRule implements Rule
{
    private bool $gte = false;
    private bool $lte = false;

    public function passes($attribute, $value): bool
    {
        if (!is_int($value)) {
            return false;
        }

        if ($value <= 0) {
            return false;
        }

        if ($this->gte) {
            return Carbon::createFromTimestamp($value)->gte(now());
        }

        if ($this->lte) {
            return Carbon::createFromTimestamp($value)->lte(now());
        }

        return true;
    }

    public function gte(): self
    {
        $this->gte = true;
        $this->lte = false;

        return $this;
    }

    public function lte(): self
    {
        $this->gte = false;
        $this->lte = true;

        return $this;
    }

    public function message(): string
    {
        return __('validation.custom.timestamp');
    }
}