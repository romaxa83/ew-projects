<?php

namespace App\Rules\Fueling;

use App\Http\Resources\Fueling\FuelingValidatedResource;
use App\Models\Fueling\Fueling;
use Illuminate\Contracts\Validation\Rule;

class FuelingDriverExist implements Rule
{
    private Fueling $fueling;

    public function __construct(Fueling $fueling)
    {
        $this->fueling = $fueling;
    }

    public function passes($attribute, $value): bool
    {
        return (bool) $this->fueling->user_id;
    }

    public function message(): string
    {
        return __('validation.invalid_driver_name');
    }
}
