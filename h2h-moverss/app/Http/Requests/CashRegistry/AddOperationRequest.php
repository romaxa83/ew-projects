<?php

namespace App\Http\Requests\CashRegistry;

use App\Enums\CashRegistry\OperationType;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOperationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $in = 'in:'. OperationType::CASH_COLLECTION->value . ',' .  OperationType::CASH_DISBURSEMENT->value . ',' . OperationType::CASH_TRANSFER->value;

        $rules = [
            'employee_id' => ['required', 'integer', Rule::exists(Employee::TABLE, 'id')],
            'insert_at' => ['required', 'string', 'date_format:Y-m-d H:i:s'],
            'sum' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'string', $in],
        ];

        if(request('type') === OperationType::CASH_TRANSFER->value){
            $rules += [
                'to_employee_id' => ['required', 'integer', 'different:employee_id', Rule::exists(Employee::TABLE, 'id')],
            ];
        }

        return $rules;
    }
}

