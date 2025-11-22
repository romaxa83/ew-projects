<?php

namespace App\Http\Requests\BodyShop\Users;

use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;

class DestroyUserRequest extends \App\Http\Requests\Users\DestroyUserRequest
{
    use OnlyValidateForm;

    protected function checkRoles(): bool
    {
        /**@var User $user*/
        $user = $this->route()->parameter('user');

        return $user->id !== $this->user()->id;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->checkRoles() && $this->user()->can('bs-users delete');
    }
}
