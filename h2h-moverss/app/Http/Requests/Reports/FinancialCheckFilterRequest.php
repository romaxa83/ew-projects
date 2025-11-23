<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class FinancialCheckFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'string'],
            'user_id' => ['nullable', 'string'],
            'division_id' => ['nullable', 'string', 'max:255'],
            'division_tz' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'string'],
            'per_page' => ['nullable', 'string'],
        ];
    }
}

