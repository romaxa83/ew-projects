<?php

namespace App\Rules\Clients;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class ERDPOURule
 * @package App\Rules
 *
 * More details:
 * @link https://1cinfo.com.ua/Article/Detail/Proverka_koda_po_EDRPOU/
 */
class EDRPOURule implements Rule
{
    public function passes($attribute, $value): bool
    {
        $numValue = (float)$value;
        if ($numValue < 30000000.00 || $numValue > 60000000.00) {
            return $this->passesFirstGroup($value);
        }
        return $this->passesSecondGroup($value);
    }

    private function passesFirstGroup(string $value): bool
    {
        $lastNum = (int)$value[7];

        $check = ((int)$value[0] + (int)$value[1] * 2 + (int)$value[2] * 3 + (int)$value[3] * 4 + (int)$value[4] * 5 + (int)$value[5] * 6 + (int)$value[6] * 7) % 11;

        if ($check < 10) {
            return $check === $lastNum;
        }
        $check = ((int)$value[0] * 3 + (int)$value[1] * 4 + (int)$value[2] * 5 + (int)$value[3] * 6 + (int)$value[4] * 7 + (int)$value[5] * 8 + (int)$value[6] * 9) % 11;

        if ($check !== 10) {
            return $check === $lastNum;
        }
        return $lastNum === 0;
    }

    private function passesSecondGroup(string $value): bool
    {
        $lastNum = (int)$value[7];

        $check = ((int)$value[0] * 7 + (int)$value[1] + (int)$value[2] * 2 + (int)$value[3] * 3 + (int)$value[4] * 4 + (int)$value[5] * 5 + (int)$value[6] * 6) % 11;

        if ($check < 10) {
            return $check === $lastNum;
        }
        $check = ((int)$value[0] * 9 + (int)$value[1] * 3 + (int)$value[2] * 4 + (int)$value[3] * 5 + (int)$value[4] * 6 + (int)$value[5] * 7 + (int)$value[6] * 8) % 11;

        if ($check !== 10) {
            return $check === $lastNum;
        }
        return $lastNum === 0;
    }

    public function message(): string
    {
        return trans('validation.custom.clients.incorrect_edrpou');
    }
}
