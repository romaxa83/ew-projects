<?php

namespace App\Http\Requests\User;

use App\Enums\Users\UserStatus;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use Illuminate\Validation\Rule;

class UserFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->orderRule(User::ALLOWED_SORTING_FIELDS),
            [
                'id' => ['nullable'],
                'status' => ['nullable', 'string', UserStatus::ruleIn()],
                'role_id' => ['nullable', 'integer', Rule::exists(Role::TABLE, 'id')],
            ]
        );
    }
}
