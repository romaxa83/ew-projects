<?php

namespace App\Http\Requests\Communications;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlowRecordFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'untill' => ['nullable', 'int'],
            'contact' => ['array'],
            'contact.type' => ['string'],
            'contact.client' => ['nullable', 'array'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
