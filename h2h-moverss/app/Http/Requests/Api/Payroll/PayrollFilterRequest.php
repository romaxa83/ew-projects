<?php

namespace App\Http\Requests\Api\Payroll;

use App\Enums\Common\DateFormat;
use Illuminate\Foundation\Http\FormRequest;

class PayrollFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'string', 'date_format:'. DateFormat::FILTER_DATE() .''],
        ];
    }
}
