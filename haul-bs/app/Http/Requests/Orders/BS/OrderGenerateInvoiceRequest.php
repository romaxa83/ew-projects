<?php

namespace App\Http\Requests\Orders\BS;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property string invoice_date
 */
class OrderGenerateInvoiceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'invoice_date' => ['nullable', 'date_format:m/d/Y',],
        ];
    }
}
