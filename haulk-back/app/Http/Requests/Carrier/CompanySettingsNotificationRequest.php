<?php

namespace App\Http\Requests\Carrier;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class CompanySettingsNotificationRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_emails' => ['nullable', 'array'],
            'notification_emails.*.value' => ['nullable', 'email', $this->email(), 'max:255'],
            'receive_bol_copy_emails' => ['nullable', 'array'],
            'receive_bol_copy_emails.*.value' => ['nullable', 'email', $this->email(), 'max:255'],
            'brokers_delivery_notification' => ['nullable', 'boolean'],
            'add_pickup_delivery_dates_to_bol' => ['nullable', 'boolean'],
            'send_bol_invoice_automatically' => ['nullable', 'boolean'],
            'is_invoice_allowed' => ['nullable', 'boolean'],
        ];
    }
}
