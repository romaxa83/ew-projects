<?php

namespace App\Rules\Catalog\Solution;

use App\Enums\Solutions\SolutionZoneEnum;
use Illuminate\Contracts\Validation\Rule;

class CountZonesRule implements Rule
{
    public function __construct(private array $args)
    {
    }

    public function passes($attribute, $value): bool
    {
        if ($this->args['zone'] === SolutionZoneEnum::SINGLE && $value === 1) {
            return true;
        }
        if ($this->args['zone'] === SolutionZoneEnum::MULTI && $value >= 2 && $value <= 6) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return __('validation.custom.catalog.solutions.incorrect_count_zones');
    }
}
