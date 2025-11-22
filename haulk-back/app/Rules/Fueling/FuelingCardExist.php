<?php

namespace App\Rules\Fueling;

use App\Models\Fueling\Fueling;
use Illuminate\Contracts\Validation\Rule;

class FuelingCardExist implements Rule
{
    private Fueling $fueling;

    public function __construct(Fueling $fueling)
    {
        $this->fueling = $fueling;
    }

    public function passes($attribute, $value): bool
    {
        return (bool) $this->fueling->fuel_card_id;
    }

    public function message(): string
    {
        return __('validation.invalid_card_number');
    }
}
