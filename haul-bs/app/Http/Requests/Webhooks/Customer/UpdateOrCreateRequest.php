<?php

namespace App\Http\Requests\Webhooks\Customer;

use App\Foundations\Http\Requests\BaseFormRequest;

class UpdateOrCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.id' => ['required', 'integer'],
            'data.first_name' => ['required', 'string'],
            'data.last_name' => ['required', 'string'],
            'data.phone' => ['nullable', 'string'],
            'data.email' => ['required', 'string'],
            'data.phone_extension' => ['nullable', 'string'],
            'data.phones' => ['nullable', 'array'],
        ];
    }
}

