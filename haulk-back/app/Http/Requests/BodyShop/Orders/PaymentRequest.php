<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Models\BodyShop\Orders\Payment;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:' . ($this->order->debt_amount ?? 0)],
            'payment_date' => ['required', 'date_format:m/d/Y'],
            'payment_method' => ['required', 'string', Rule::in(array_keys(Payment::PAYMENT_METHODS))],
            'notes' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string'],
        ];
    }
}
