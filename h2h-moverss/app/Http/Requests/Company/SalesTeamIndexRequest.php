<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class SalesTeamIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:"Y-m"'],
        ];
    }
}
