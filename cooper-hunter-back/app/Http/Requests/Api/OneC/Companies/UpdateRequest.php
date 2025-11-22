<?php

namespace App\Http\Requests\Api\OneC\Companies;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'authorization_code' => [
                'nullable',
                'string',
                Rule::unique('companies', 'code')->ignore(
                    $this->guid,
                    'guid'
                )
            ],
            'terms' => ['nullable', 'array'],
            'terms.*.name' => ['required', 'string'],
            'terms.*.guid' => ['required', 'string'],
            'corporation' => ['sometimes', 'array'],
            'corporation.guid' => ['required_with:corporation', 'string'],
            'corporation.name' => ['required_with:corporation', 'string'],
            'manager' => ['sometimes', 'array'],
            'manager.name' => ['required_with:manager', 'string'],
            'manager.phone' => ['required_with:manager', 'string'],
            'manager.email' => ['required_with:manager', 'string'],
            'commercial_manager' => ['sometimes', 'array'],
            'commercial_manager.name' => ['required_with:commercial_manager', 'string'],
            'commercial_manager.phone' => ['nullable', 'string'],
            'commercial_manager.email' => ['required_with:commercial_manager', 'string'],
        ];
    }
}
