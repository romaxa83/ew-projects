<?php

namespace App\Rules\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class DisplayInWebRule implements Rule
{
    public function __construct(public Feature|int|null $ignore = null)
    {
    }

    public function passes($attribute, $value): bool
    {
        if (!$value) {
            return true;
        }

        return Feature::query()
                ->when($this->ignore, fn(Builder $b) => $b->where('id', '<>', $this->getIgnoreId()))
                ->where('display_in_web', true)
                ->count() < config('catalog.features.display_in_web_count');
    }

    protected function getIgnoreId(): int
    {
        return $this->ignore instanceof Feature
            ? $this->ignore->id
            : $this->ignore;
    }

    public function message(): string
    {
        return __(
            'validation.custom.catalog.features.display_in_web',
            ['count' => config('catalog.features.display_in_web_count')]
        );
    }
}
