<?php

namespace App\Http\Requests\Order\Payrolls;

use App\Http\Requests\JsonRequest;

class UpdatePayrollRequest extends JsonRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cash_collecte' => ['required', 'numeric'],
            'items.*' => ['required', 'array'],
            'items.*.employee_id' => ['required', 'numeric'],
            'items.*.role_id' => ['required', 'numeric'],
            'items.*.hourly_rate' => ['required', 'numeric'],
            'items.*.hours' => ['required', 'numeric'],
            'items.*.extras' => ['required', 'numeric'],
            'items.*.is_cc_due' => ['required', 'boolean'],
        ];
    }
}
