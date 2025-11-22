<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Expense;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'expenses.*.id' => ['nullable', 'integer', 'exists:App\Models\Orders\Expense,id'],
            'expenses.*.type_id' => ['required', 'integer', Rule::in(array_keys(Expense::EXPENSE_TYPES))],
            'expenses.*.price' => ['required', 'numeric'],
            'expenses.*.date' => ['required', 'date_format:m/d/Y'],
            'expenses.*.' . Expense::RECEIPT_FIELD_NAME => ['nullable', 'file'],
            'expenses.*.to' => [
                Rule::in([
                    Payment::PAYER_BROKER,
                    Payment::PAYER_CUSTOMER,
                    Payment::PAYER_NONE
                ])
            ],
        ];
    }
}
