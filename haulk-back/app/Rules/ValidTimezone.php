<?php


namespace App\Rules;


use Carbon\CarbonTimeZone;
use Illuminate\Contracts\Validation\Rule;

class ValidTimezone implements Rule
{
    public function passes($attribute, $value): bool
    {
        $tz = new CarbonTimeZone($value);

        return $tz && $tz->getName() === $value;
    }

    public function message()
    {
        return trans('Invalid timezone.');
    }
}
