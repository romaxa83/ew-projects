<?php

namespace App\Http\Requests\Dispatch\Ajax;

use App\Http\Requests\JsonRequest;

/**
 * @property-read string start_date
 * @property-read int|null page
 * @property-read int|null per_page
 * @property-read string|null sort_type
 * @property-read bool|null logs_all
 */

class LogRequest extends JsonRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'required|string',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'sort_type' => 'nullable|string|in:asc,desc',
            'logs_all' => 'nullable',
        ];
    }
}
