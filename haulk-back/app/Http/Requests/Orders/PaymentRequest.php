<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $request = $this;

        return [
            'payment.terms' => ['nullable', 'string', 'max:5000'],

            'payment.invoice_notes' => ['nullable', 'string', 'max:5000'],

            'payment.total_carrier_amount' => ['required', 'numeric', 'gt:0'],

            'payment.customer_payment_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.total_carrier_amount') - (float)$request->input('payment.broker_payment_amount') > 0;
                })
            ],
            'payment.customer_payment_method_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.customer_payment_amount') > 0;
                }),
                Rule::in(
                    array_keys(Payment::CUSTOMER_METHODS)
                )
            ],
            'payment.customer_payment_location' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.customer_payment_amount') > 0;
                }),
                Rule::in(
                    array_keys(Order::LOCATIONS)
                )
            ],

            'payment.broker_payment_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.total_carrier_amount') - (float)$request->input('payment.customer_payment_amount') > 0;
                })
            ],
            'payment.broker_payment_method_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_payment_amount') > 0;
                }),
                Rule::in(
                    array_keys(Payment::BROKER_METHODS)
                )
            ],
            'payment.broker_payment_days' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_payment_amount') > 0;
                }),
                Rule::in(config('orders.payment.days'))
            ],
            'payment.broker_payment_begins' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_payment_amount') > 0;
                }),
                Rule::in(
                    array_keys(Order::TERMS_BEGINS)
                )
            ],

            'payment.broker_fee_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.customer_payment_amount') + (float)$request->input('payment.broker_payment_amount') > (float)$request->input('payment.total_carrier_amount');
                })
            ],
            'payment.broker_fee_method_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_fee_amount') > 0;
                }),
                Rule::in(
                    array_keys(Payment::CARRIER_METHODS)
                )
            ],
            'payment.broker_fee_days' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_fee_amount') > 0;
                }),
                Rule::in(config('orders.payment.days'))
            ],
            'payment.broker_fee_begins' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return (float)$request->input('payment.broker_fee_amount') > 0;
                }),
                Rule::in([
                    Order::LOCATION_DELIVERY,
                    Order::LOCATION_PICKUP
                ])
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->input('payment.customer_payment_amount')) {
            $payment = $this->input('payment');

            $payment['customer_payment_amount'] = null;
            $payment['customer_payment_method_id'] = null;
            $payment['customer_payment_location'] = null;

            $this->merge(
                [
                    'payment' => $payment
                ]
            );
        }

        if (!$this->input('payment.broker_payment_amount')) {
            $payment = $this->input('payment');

            $payment['broker_payment_amount'] = null;
            $payment['broker_payment_method_id'] = null;
            $payment['broker_payment_days'] = null;
            $payment['broker_payment_begins'] = null;

            $this->merge(
                [
                    'payment' => $payment
                ]
            );
        }

        if (!$this->input('payment.broker_fee_amount')) {
            $payment = $this->input('payment');

            $payment['broker_fee_amount'] = null;
            $payment['broker_fee_method_id'] = null;
            $payment['broker_fee_days'] = null;
            $payment['broker_fee_begins'] = null;

            $this->merge(
                [
                    'payment' => $payment
                ]
            );
        }
    }
}
