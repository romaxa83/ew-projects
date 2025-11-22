<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class DispatcherAccessToDriver implements Rule
{

    private User $dispatcher;

    /**
     * Create a new rule instance.
     * @param User $dispatcher
     * @return void
     */
    public function __construct(User $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        return $this->dispatcher->id === $value->owner_id || $this->dispatcher->isAdmin();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('You don\'t have access to driver.');
    }
}
