<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'account_id' => 'required|exists:payments_accounts,id',
            'description' => 'nullable|string|max:65536',
            'amount' => 'required|numeric|between:-999999,999999.99',
            'in_total' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['description'] = strip_tags($input['description'], '<br/>');
        $input['in_total'] = !empty($input['in_total']) ? 1 : 0;

        $this->replace($input);
    }
}
