<?php

namespace App\Http\Requests\Api\Payroll;

use App\Models\Employee;
use App\Models\User\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.employee_id' => ['required', 'integer', Rule::exists(Employee::TABLE, 'id')],
            'items.*.role_id' => ['required', 'integer', Rule::exists(Role::TABLE, 'id')],
            'items.*.rate' => ['required', 'numeric'],
            'items.*.extra' => ['required', 'numeric'],
            'items.*.is_cc_due' => ['required', 'boolean'],
        ];
    }
}
