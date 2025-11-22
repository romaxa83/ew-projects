<?php

namespace App\Http\Requests\Api\OneC\Users;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Users\User;
use App\Permissions\Users\UserUpdatePermission;

class UserUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = User::class;

    public function authorize(): bool
    {
        return $this->user()->can(UserUpdatePermission::KEY);
    }
}
