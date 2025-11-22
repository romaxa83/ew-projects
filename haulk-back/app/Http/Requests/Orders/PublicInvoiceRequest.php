<?php

namespace App\Http\Requests\Orders;

use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_recipient' => [
                'required',
                'string'
            ],
            'invoice_id' => [
                'nullable',
                'string',
                'max:255'
            ],
            'invoice_date' => [
                'nullable',
                'date_format:"M d, Y"'
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'invoice_recipient' => $this->route()->parameter('recipient')
        ]);
    }

    public function dto(): array
    {
        $data = $this->validated();
        return [
            'recipient' => $data['invoice_recipient'],
            'id' => !empty($data['invoice_id']) ? $data['invoice_id'] : null,
            'date' => !empty($data['invoice_date']) ? Carbon::createFromFormat('M d, Y', $data['invoice_date']) : null
        ];
    }



    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isEmpty()) {
                return;
            }

            throw new NotFoundHttpException();
        });
    }
}
