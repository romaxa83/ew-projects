<?php

namespace App\Http\Requests\Api\OneC\Users;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Users\UserListPermission;
use JetBrains\PhpStorm\Pure;

class UsersIndexRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(UserListPermission::KEY);
    }

    #[Pure] public function rules(): array
    {
        return $this->getPaginationRules();
    }
}
