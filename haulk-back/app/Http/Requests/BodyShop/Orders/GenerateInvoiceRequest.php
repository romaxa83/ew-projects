<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string invoice_date
 */
class GenerateInvoiceRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $request = $this;

        return [
            'invoice_date' => [
                'nullable',
                'date_format:m/d/Y',
            ],
        ];
    }
}
