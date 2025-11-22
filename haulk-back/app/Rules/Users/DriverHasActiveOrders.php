<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use App\Services\Users\UserService;
use Illuminate\Contracts\Validation\Rule;

class DriverHasActiveOrders implements Rule
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
        /**@var UserService $service*/
        $service = resolve(UserService::class);
        return $service->driverHasActiveOrders($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('Driver doesn\'t have active orders.');
    }
}
