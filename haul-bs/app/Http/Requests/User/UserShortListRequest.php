<?php

namespace App\Http\Requests\User;

use App\Enums\Users\UserStatus;
use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Foundations\Modules\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'roles' => ['nullable', 'required_without:id',  'nullable'],
                'roles.*' => ['integer', 'required', Rule::exists(Role::TABLE, 'id'),],
                'statuses' => ['nullable', 'nullable'],
                'statuses.*' => ['string', 'required', UserStatus::ruleIn()],
            ]
        );
    }
}
