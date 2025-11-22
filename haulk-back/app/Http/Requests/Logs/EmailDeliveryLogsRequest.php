<?php

namespace App\Http\Requests\Logs;

use App\Services\Logs\DeliveryLogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailDeliveryLogsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'logs' => ['array'],
            'logs.*.order_id' => ['required', 'string'],
            'logs.*.recipient_email' => ['required', 'email'],
            'logs.*.type' => ['required', 'string'],
            'logs.*.result' => ['required', 'string', Rule::in([DeliveryLogService::EMAIL_RESULT_SUCCESS, DeliveryLogService::EMAIL_RESULT_FAIL])],
            'logs.*.env_type' => ['nullable', 'string']
        ];
    }
}
