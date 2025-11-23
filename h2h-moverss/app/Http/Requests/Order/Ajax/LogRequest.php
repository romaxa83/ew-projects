<?php

namespace App\Http\Requests\Order\Ajax;

use App\Http\Requests\JsonRequest;

/**
 * @property-read int order_id
 * @property-read int|null user_id
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
            'order_id' => 'required|integer|exists:orders,id',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'user_id' => 'nullable|integer|exists:users,id',
            'sort_type' => 'nullable|string|in:asc,desc',
            'logs_all' => 'nullable',
        ];
    }
}
