<?php

namespace WezomCms\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhonePatterns implements Rule
{
    protected $patterns = [
        "/^\+380\d{9}$/",
        "/^\+7\d{10}$/",

    ];

    public function __construct()
    {}

    public function passes($attribute, $value): bool
    {
        $value = prettyPhone($value);

        $temp = [];
        foreach ($this->patterns ?? [] as  $pattern) {
            $temp[] = preg_match($pattern, $value) > 0;
        }

        return in_array(true, $temp);
    }

    public function message(): string|array
    {
        return __('cms-core::admin.validation.wrong_phone_format');
    }
}

