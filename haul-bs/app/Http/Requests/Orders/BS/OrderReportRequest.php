<?php

namespace App\Http\Requests\Orders\BS;

use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

class OrderReportRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->orderRule(['current_due', 'past_due', 'total_due']),
            [
                'statuses' => ['nullable', 'array'],
                'statuses.*' => ['required', 'string', EnumHelper::ruleIn(OrderStatus::class)],
                'payment_statuses' => ['nullable', 'array'],
                'payment_statuses.*' => ['required', 'string', EnumHelper::ruleIn(OrderPaymentStatus::class)],
                'implementation_date_from' => ['nullable', 'date'],
                'implementation_date_to' => ['nullable', 'date'],
            ]
        );
    }
}
