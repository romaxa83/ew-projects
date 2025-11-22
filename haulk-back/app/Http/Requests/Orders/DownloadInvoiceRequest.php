<?php

namespace App\Http\Requests\Orders;

use App\Models\Orders\Payment;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DownloadInvoiceRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return $this->user()->can('orders read');
    }

    public function rules(): array
    {
        return [
            'invoice_recipient' => [
                'required',
                'string',
                Rule::in([
                    Payment::PAYER_BROKER,
                    Payment::PAYER_CUSTOMER
                ])
            ]
        ];
    }

    public function invoiceRecipient(): string
    {
        return $this->validated()['invoice_recipient'];
    }
}
