<?php

namespace App\Http\Requests\Api\OneC\Technicians;

use App\Http\Requests\BaseFormRequest;
use App\Models\Locations\State;
use App\Permissions\Technicians\TechnicianCreatePermission;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use Illuminate\Validation\Rule;

class TechnicianCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(TechnicianCreatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'state_id' => ['required', 'int', Rule::exists(State::TABLE, 'id')],
            'license' => ['required', 'string'],
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule()],
            'phone' => ['nullable', 'string', new MemberUniquePhoneRule()],
            'password' => ['required', 'string', new PasswordRule(), 'confirmed'],
            'guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
