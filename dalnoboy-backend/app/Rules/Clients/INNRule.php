<?php

namespace App\Rules\Clients;

use Illuminate\Contracts\Validation\Rule;

class INNRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $lastNum = (int)$value[9];

        $check = ((int)$value[0] * (-1) + (int)$value[1] * 5 + (int)$value[2] * 7 + (int)$value[3] * 9 + (int)$value[4] * 4 + (int)$value[5] * 6 + (int)$value[6] * 10 + (int)$value[7] * 5 + (int)$value[8] * 7) % 11;

        if ($check === 10) {
            $check = 0;
        }

        return $lastNum === $check;
    }

    public function message(): string
    {
        return trans('validation.custom.clients.incorrect_inn');
    }
}
