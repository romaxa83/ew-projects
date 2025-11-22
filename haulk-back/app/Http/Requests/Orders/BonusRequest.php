<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BonusRequest extends FormRequest
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
            'bonuses.*.id' => ['nullable', 'integer', 'exists:App\Models\Orders\Bonus,id'],
            'bonuses.*.type' => ['required', 'string'],
            'bonuses.*.price' => ['required', 'numeric'],
            'bonuses.*.to' => [
                Rule::in([
                    Payment::PAYER_BROKER,
                    Payment::PAYER_CUSTOMER,
                    Payment::PAYER_NONE
                ])
            ],
        ];
    }
}
