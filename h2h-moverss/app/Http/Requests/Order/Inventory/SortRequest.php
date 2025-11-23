<?php

namespace App\Http\Requests\Order\Inventory;

use App\Http\Requests\JsonRequest;

class SortRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.sort' => ['required', 'integer'],
            'items.*.section_id' => ['required', 'integer'],
        ];
    }
}
