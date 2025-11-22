<?php

namespace App\Http\Requests\Orders\Parts;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

class OrderFilterRequest extends BaseFormRequest
{
    private const PER_PAGE = 10;

    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'search_inventory' => ['nullable', 'string'],
            'search_customer' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', OrderStatus::ruleIn()],
            'source' => ['nullable', 'string', OrderSource::ruleIn()],
            'payment_status' => ['nullable', 'string', OrderPaymentStatus::ruleIn()],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sales_manager_id' => ['nullable', 'integer'],
        ];
    }
}
