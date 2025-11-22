<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;

class OrderAddInvoiceDataRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'order_guid' => ['nullable', 'string', 'required_without:packing_slip_guid'],
            'packing_slip_guid' => ['nullable', 'string', 'required_without:order_guid'],
            'invoice' => ['nullable', 'string'],
            'invoice_date' => ['nullable', 'date_format:Y-m-d'],
            'tax' => ['nullable', 'numeric'],
            'shipping_price' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'total_discount' => ['nullable', 'numeric'],
            'total_with_discount' => ['nullable', 'numeric'],
        ];
    }
}
