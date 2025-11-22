<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueValuesByFieldRule implements Rule
{
    public function __construct(protected string $field)
    {
    }

    public function passes($attribute, $value): bool
    {
        $collection = collect($value);

        return $collection->count() === $collection->pluck($this->field)->unique()->count();
    }

    public function message(): string
    {
        return __('validation.all_values_are_not_unique', ['field' => $this->field]);
    }
}
