<?php

namespace App\Rules\Catalog\Tickets;

use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Contracts\Validation\Rule;

class TicketApiOrderPartRule implements Rule
{
    protected string $attribute;

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        if (is_string($value)) {
            return true;
        }

        if (!$guid = data_get($value, 'guid')) {
            return false;
        }

        if (OrderCategory::query()->where('guid', $guid)->doesntExist()) {
            return false;
        }

        return (bool)data_get($value, 'value');
    }

    public function message(): string
    {
        return __('validation.regex', ['attribute' => $this->attribute]);
    }
}