<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ReportForemanCashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:all,processed,unprocessed'],
            'start_range' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'end_range' => ['nullable', 'string', 'date_format:"Y-m-d"'],
            'employee_id' => ['nullable', 'string'],
            'page' => ['nullable', 'string'],
            'per_page' => ['nullable', 'string'],
        ];
    }
}

