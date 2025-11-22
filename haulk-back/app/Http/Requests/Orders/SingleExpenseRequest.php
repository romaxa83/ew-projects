<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SingleExpenseRequest extends FormRequest
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
            'type' => ['nullable', 'integer'],
            'price' => ['nullable', 'numeric'],
            'date' => ['nullable', 'date'],
            'receipt' => ['nullable', 'file'],
            'to' => [
                'nullable',
                Rule::in([
                    Payment::PAYER_BROKER,
                    Payment::PAYER_CUSTOMER
                ])
            ],
        ];
    }
}
