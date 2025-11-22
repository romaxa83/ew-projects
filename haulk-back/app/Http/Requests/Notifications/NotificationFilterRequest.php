<?php

namespace App\Http\Requests\Notifications;

use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class NotificationFilterRequest extends FormRequest
{
    use OnlyValidateForm;
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'int'],
            'per_page' => ['nullable', 'int'],
            'status' => ['nullable', 'string', NotificationStatus::ruleIn()],
            'type' => ['nullable', 'string', NotificationType::ruleIn()],
            'place' => ['nullable', 'string', NotificationPlace::ruleIn()],
        ];
    }
}
