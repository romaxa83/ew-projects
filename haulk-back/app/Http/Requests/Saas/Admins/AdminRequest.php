<?php

namespace App\Http\Requests\Saas\Admins;

use App\Dto\Saas\Admins\AdminDto;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Admins\Admin;
use App\Traits\ValidationRulesTrait;
use Illuminate\Validation\Rule;

class AdminRequest extends BaseSassRequest
{
    use ValidationRulesTrait;

    public function rules(): array
    {
        $admin = $this->route('admin');

        return [
            'full_name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique(Admin::TABLE, 'email')
                    ->ignore(optional($admin)->id)
            ],
            'phone' => ['nullable', 'string', $this->USAPhone()],
            'password' => ['nullable', 'string', $this->passwordRule()],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }

    public function getDto(): AdminDto
    {
        return AdminDto::byParams($this->validated());
    }
}
