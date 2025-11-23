<?php

namespace App\Http\Requests\CashRegistry;

use App\Enums\CashRegistry\OperationType;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperationFilterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => ['nullable', 'integer', Rule::exists(Employee::TABLE, 'id')],
            'start_date' => ['nullable', 'string', 'date_format:Y-m-d H:i:s'],
            'end_date' => ['nullable', 'string', 'date_format:Y-m-d H:i:s'],
            'type' => ['nullable', 'string', OperationType::ruleIn()],
            'page' => ['nullable', 'int'],
        ];
    }
}

