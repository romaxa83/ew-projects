<?php

namespace App\Http\Requests\Api\OneC\Technicians;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Technicians\TechnicianCreatePermission;
use App\Rules\MemberUniqueEmailRule;
use App\Rules\MemberUniquePhoneRule;
use App\Rules\NameRule;
use App\Rules\PasswordRule;
use App\Rules\UniqueValuesByFieldRule;

class TechniciansImportRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(TechnicianCreatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'technicians' => [
                'required',
                'array',
                'min:1',
                new UniqueValuesByFieldRule('email'),
                new UniqueValuesByFieldRule('phone')
            ],
            'technicians.*.first_name' => ['required', 'string', new NameRule('first_name')],
            'technicians.*.last_name' => ['required', 'string', new NameRule('last_name')],
            'technicians.*.email' => ['required', 'string', 'email:filter', new MemberUniqueEmailRule()],
            'technicians.*.phone' => ['nullable', 'string', new MemberUniquePhoneRule()],
            'technicians.*.password' => ['required', 'string', new PasswordRule(), 'confirmed'],
            'technicians.*.guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
