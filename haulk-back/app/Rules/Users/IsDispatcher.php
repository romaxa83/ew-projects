<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class IsDispatcher implements Rule
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
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value->getRoleName(), [User::DISPATCHER_ROLE, User::ADMIN_ROLE, User::SUPERADMIN_ROLE]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('User must be dispatcher.');
    }
}
