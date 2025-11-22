<?php

namespace App\Http\Requests\Saas\Invoices;

use App\Http\Requests\Saas\BaseSassRequest;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class InvoiceListRequest extends BaseSassRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return parent::rules() + [
                'company_id' => [
                    'nullable',
                    'integer',
                    'exists:\App\Models\Billing\Invoice,carrier_id'
                ],
                'dates_range' => [
                    'nullable',
                    'regex:/([0-9]{2}\/[0-9]{2}\/[0-9]{4})\s*\-\s*([0-9]{2}\/[0-9]{2}\/[0-9]{4})/'
                ],
                'payment_status' => [
                    'nullable',
                    Rule::in(['all', 'paid', 'not_paid']),
                ],
                'paid_dates_range' => [
                    'nullable',
                    'regex:/(\d{2}\/\d{2}\/\d{4})\s*\-\s*(\d{2}\/\d{2}\/\d{4})/'
                ],
                'has_gps_subscription' => [
                    'nullable', 'boolean'
                ],
            ];
    }

    public function orderBy(): string
    {
        return 'in:' . implode(
                ',',
                [
                    'company_name',
                ]
            );
    }
}
