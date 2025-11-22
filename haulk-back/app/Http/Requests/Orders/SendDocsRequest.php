<?php

namespace App\Http\Requests\Orders;

use App\Dto\Orders\SendDocsDto;
use App\Models\Orders\Payment;
use App\Models\Saas\Company\Company;
use App\Rules\Orders\IsNeedW9;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @property array send_via
 * @property array content
 * @property array recipient_email
 * @property string recipient_fax
 */
class SendDocsRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return $this->user()->can('orders send-invoice');
    }

    public function rules(): array
    {
        $request = $this;

        $content = $this->input('content');
        return [
            'send_via' => [
                'required',
                'array'
            ],
            'send_via.*' => [
                'required',
                'string',
                'distinct',
                'in:fax,email'
            ],
            'recipient_email' => [
                'array',
                'nullable'
            ],
            'recipient_email.*.value' => [
                Rule::requiredIf(
                    fn() => is_array($request->send_via) && in_array('email', $request->send_via, true)
                ),
                'nullable',
                'email'
            ],
            'recipient_fax' => [
                Rule::requiredIf(
                    fn() => is_array($request->send_via) && in_array('fax', $request->send_via, true)
                ),
                'nullable',
                $this->USAPhone()
            ],

            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:App\Models\Orders\Order,id,deleted_at,NULL'],
            'orders.*.invoice_id' => [
                'string',
                Rule::requiredIf(
                    fn() => is_array($content) && in_array('invoice', $content, true)
                ),
                'nullable'
            ],
            'orders.*.invoice_date' => [
                Rule::requiredIf(
                    fn() => is_array($content) && in_array('invoice', $content, true)
                ),
                'nullable',
                'date_format:m/d/Y',
            ],
            'invoice_recipient' => [
                'string',
                Rule::requiredIf(
                    fn() => is_array($content) && in_array('invoice', $content, true)
                ),
                Rule::in([
                    Payment::PAYER_BROKER,
                    Payment::PAYER_CUSTOMER
                ])
            ],
            'orders.*.show_shipper_info' => ['nullable', 'boolean'],

            'content' => [
                'required',
                'array'
            ],
            'content.*' => [
                'required',
                'string',
                'in:invoice,bol,w9',
                'distinct'
            ],
            'w9' => [
                new IsNeedW9($request)
            ]
        ];
    }

    public function dto(): SendDocsDto
    {
        return SendDocsDto::create()->origin($this->validated());
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('orders') && is_array($this->input('orders'))) {
            $orders = $this->input('orders');

            foreach ($orders as &$order) {
                $order['show_shipper_info'] = isset($order['show_shipper_info'])
                    ? (bool)$order['show_shipper_info']
                    : false;
            }

            $this->merge(['orders' => $orders]);
        }

        /**@var Company $company*/
        $company = $this->user()
            ->getCompany();

        $this->merge([
            'w9' => $company->getFirstMedia(Company::W9_FIELD_CARRIER)
        ]);

        $this->transformPhoneAttribute('recipient_fax');
    }

    public function withValidator(Validator $validator): void
    {
        $request = $this;
        $validator->after(function (Validator $validator) use ($request) {
            if ($validator->errors()->has('w9')) {
                $validator->errors()->add('content', $validator->errors()->first('w9'));
            }
            if ($validator->errors()->has('content.*')) {
                $errors = $validator->errors()->get('content.*');
                $content = $request->get('content');
                foreach ($errors as $key => $error) {
                    $key = (int)str_replace("content.", "", $key);
                    $validator->errors()->add('content', trans('validation.in', ['attribute' => 'parameter "' . $content[$key] . '"']));
                }
            }
        });
    }

}
