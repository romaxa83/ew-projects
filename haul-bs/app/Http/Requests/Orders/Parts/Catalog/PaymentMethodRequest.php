<?php

namespace App\Http\Requests\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\PaymentTerms;
use App\Foundations\Http\Requests\BaseFormRequest;

class PaymentMethodRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'payment_terms' => ['nullable', 'string', PaymentTerms::ruleIn()],
            'for_add_payment' => ['nullable', 'boolean'],
        ];
    }
}
