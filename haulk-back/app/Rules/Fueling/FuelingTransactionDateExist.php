<?php

namespace App\Rules\Fueling;

use App\Models\Fueling\Fueling;
use DateTime;
use Illuminate\Contracts\Validation\Rule;

class FuelingTransactionDateExist implements Rule
{
    public function passes($attribute, $value): bool
    {
        $format = 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat('!' . $format, $value);
        return $date && $date->format($format) == $value;
    }

    public function message(): string
    {
        return __('validation.transaction_date_validation');
    }
}
