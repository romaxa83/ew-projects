<?php

namespace App\Http\Requests\Api\CashRegistry;

use App\Enums\Common\DateFormat;
use Illuminate\Foundation\Http\FormRequest;

class CashRegistryItemsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'int'],
            'per_page' => ['nullable', 'int'],
            'start_date' => ['nullable', 'string', 'date_format:'. DateFormat::FILTER_DATE()],
            'end_date' => ['nullable', 'string', 'date_format:'. DateFormat::FILTER_DATE()],
        ];
    }
}
