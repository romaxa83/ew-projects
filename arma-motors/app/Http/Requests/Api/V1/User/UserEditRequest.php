<?php

namespace App\Http\Requests\Api\V1\User;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => ['nullable', 'string'],
            'status' => ['nullable', 'integer'],
            'newPhone' => ['nullable', 'string'],
            'codeOKPO' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'verify' => ['nullable', 'boolean'],
            'email' => ['nullable','email', 'string',
                Rule::unique(User::TABLE_NAME, 'email')->ignore($this->route('id'), "uuid")
            ],
        ];
    }
}
