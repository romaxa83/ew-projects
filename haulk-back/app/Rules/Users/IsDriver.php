<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class IsDriver implements Rule
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
     * @param  User  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return in_array($value->getRoleName(), User::DRIVER_ROLES, true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('User must be driver.');
    }
}
