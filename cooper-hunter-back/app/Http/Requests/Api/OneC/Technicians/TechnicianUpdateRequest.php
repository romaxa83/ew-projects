<?php

namespace App\Http\Requests\Api\OneC\Technicians;

use App\Http\Requests\BaseFormRequest;
use App\Models\Locations\State;
use App\Permissions\Technicians\TechnicianUpdatePermission;
use App\Rules\ExistsLanguages;
use App\Rules\NameRule;
use Illuminate\Validation\Rule;

class TechnicianUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(TechnicianUpdatePermission::KEY);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', new NameRule('first_name')],
            'state_id' => ['nullable', 'int', Rule::exists(State::TABLE, 'id')],
            'license' => ['nullable', 'string'],
            'last_name' => ['required', 'string', new NameRule('last_name')],
            'email' => ['required', 'string', 'email:filter'],
            'phone' => ['nullable', 'string'],
            'lang' => ['nullable', 'string', 'min:2', 'max:3', new ExistsLanguages()],
            'guid' => ['nullable', 'string', 'uuid']
        ];
    }
}
