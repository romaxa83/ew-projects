<?php

namespace App\Rules\Catalog\Categories;

use App\Models\Catalog\Categories\Category;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class MainCategoryRule implements Rule
{
    public function __construct(public Category|int|null $ignore = null)
    {
    }

    public function passes($attribute, $value): bool
    {
        if (!$value) {
            return true;
        }

        return Category::query()
                ->when($this->ignore, fn(Builder $b) => $b->where('id', '<>', $this->getIgnoreId()))
                ->where('main', true)
                ->count() < config('catalog.categories.main_count');
    }

    protected function getIgnoreId(): int
    {
        return $this->ignore instanceof Category
            ? $this->ignore->id
            : $this->ignore;
    }

    public function message(): string
    {
        return __(
            'validation.custom.catalog.categories.main',
            ['count' => config('catalog.categories.main_count')]
        );
    }
}
