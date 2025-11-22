<?php

namespace App\Http\Requests\Notifications;

use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class NotificationReadRequest extends FormRequest
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
            'id' => ['required', 'array'],
        ];
    }
}

