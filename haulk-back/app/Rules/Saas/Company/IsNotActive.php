<?php

namespace App\Rules\Saas\Company;

use App\Models\Saas\Company\Company;
use Illuminate\Contracts\Validation\Rule;

class IsNotActive implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Company  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $value->isActive() === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('company.Failed to delete active company');
    }
}
