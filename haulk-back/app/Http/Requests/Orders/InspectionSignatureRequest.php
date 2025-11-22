<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectionSignatureRequest extends FormRequest
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
        $request = $this;

        return [
            'customer_not_available' => [
                'required',
                'boolean',
            ],
            'customer_refused_to_sign' => [
                'required',
                'boolean',
            ],
            'customer_full_name' => [
                Rule::requiredIf(function () use ($request) {
                    if (
                        !$request->boolean('customer_not_available')
                        || $request->boolean('customer_refused_to_sign')
                    ) {
                        return true;
                    }

                    return false;
                }),
                'string',
                'max:255',
            ],
            Order::CUSTOMER_SIGNATURE_FIELD_NAME => [
                Rule::requiredIf(function () use ($request) {
                    if (
                        !$request->boolean('customer_not_available')
                        && !$request->boolean('customer_refused_to_sign')
                    ) {
                        return true;
                    }

                    return false;
                }),
                'file',
            ],
            Order::DRIVER_SIGNATURE_FIELD_NAME => [
                'required',
                'file',
            ],
            'actual_date' => ['nullable', 'integer'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'customer_not_available' => $this->boolean('customer_not_available'),
            'customer_refused_to_sign' => $this->boolean('customer_refused_to_sign'),
        ]);
    }
}
