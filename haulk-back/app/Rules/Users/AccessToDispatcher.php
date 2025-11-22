<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class AccessToDispatcher implements Rule
{

    private User $owner;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $owner)
    {
        $this->owner = $owner;
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
        return $this->owner->id === $value->owner_id || $this->owner->isAdmin();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('You don\'t have access to dispatcher.');
    }
}
