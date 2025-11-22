<?php

namespace App\Http\Requests\V2\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property Order order
 */
class DriverPaymentDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $request = $this;

        return [
            'driver_payment_amount' => [
                Rule::requiredIf(
                    function () use ($request) {
                        if (
                            !$request->hasAny(
                                [
                                    'driver_payment_uship_code',
                                    Order::DRIVER_PAYMENT_FIELD_NAME,
                                    'driver_payment_comment',
                                ]
                            )
                        ) {
                            return true;
                        }

                        return false;
                    }
                ),
                'numeric',
                'max:1000000'
            ],
            'driver_payment_method_id' => [
                'sometimes',
                Rule::in(
                    array_keys(Payment::CUSTOMER_METHODS)
                ),
            ],
            'driver_payment_uship_code' => [
                Rule::requiredIf(
                    function () use ($request) {
                        if (
                            !$request->hasAny(
                                [
                                    'driver_payment_amount',
                                    Order::DRIVER_PAYMENT_FIELD_NAME,
                                    'driver_payment_comment',
                                ]
                            )
                        ) {
                            return true;
                        }

                        return false;
                    }
                ),
                'string',
            ],
            'driver_payment_account_type' => [
                'nullable',
                Rule::requiredIf(
                    function () use ($request) {
                        if (
                            in_array(
                                (int)$request->input('driver_payment_method_id'),
                                [
                                    Payment::METHOD_CASHAPP,
                                    Payment::METHOD_ZELLE,
                                ],
                                true
                            )
                        ) {
                            return true;
                        }

                        return false;
                    }
                ),
                Rule::in(['personal', 'company'])
            ],
            Order::DRIVER_PAYMENT_FIELD_NAME => [
                Rule::requiredIf(
                    function () use ($request) {
                        if (
                            !$request->hasAny(
                                [
                                    'driver_payment_amount',
                                    'driver_payment_uship_code',
                                    'driver_payment_comment',
                                ]
                            )
                        ) {
                            return true;
                        }

                        return false;
                    }
                ),
                'file',
                'mimetypes:application/pdf,image/jpeg,image/png'
            ],
            'driver_payment_comment' => [
                Rule::requiredIf(
                    function () use ($request) {
                        if (
                            !$request->hasAny(
                                [
                                    'driver_payment_amount',
                                    'driver_payment_uship_code',
                                    Order::DRIVER_PAYMENT_FIELD_NAME,
                                ]
                            )
                        ) {
                            return true;
                        }

                        return false;
                    }
                ),
                'string',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if (in_array((int)$this->input('driver_payment_method_id'), [Payment::METHOD_COP, Payment::METHOD_COD], true)) {
            $this->merge(
                [
                    'driver_payment_method_id' => Payment::METHOD_CASH,
                ]
            );
        }
    }
}
