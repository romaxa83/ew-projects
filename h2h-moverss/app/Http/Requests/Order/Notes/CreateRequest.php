<?php

namespace App\Http\Requests\Order\Notes;

use App\Http\Requests\JsonRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

/**
 * @property-read int order_id
 * @property-read string| text
 * @property-read boolean|null is_pinned
 * @property-read string|null returnFormat
 */

class CreateRequest extends JsonRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_id' => ['required', 'integer' , Rule::exists(Order::TABLE, 'id')],
            'text' => ['required', 'string', 'min:1', 'max:65536'],
            'is_pinned' => ['nullable', 'boolean'],
            'returnFormat' => ['nullable', 'string'],
        ];
    }
}
