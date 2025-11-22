<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Payment;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStageRequest extends FormRequest
{
    use OnlyValidateForm;

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
            'amount' => ['required', 'numeric'],
            'payment_date' => ['required', 'date_format:m/d/Y'],
            'payer' => ['required', 'string', Rule::in([Payment::PAYER_CUSTOMER, Payment::PAYER_BROKER, Payment::PAYER_CARRIER])],
            'method_id' => ['required', 'integer', Rule::in(array_keys(Payment::ALL_METHODS))],
            'uship_number' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
