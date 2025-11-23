<?php

namespace App\Http\Requests\Order\Inventory;

use App\Http\Requests\JsonRequest;

class CreateRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // room or item, room = 1, item = 0
            'is_section' => ['required', 'in:1,0'],
            'section_id' => ['nullable', 'integer'], // room relation
            'price' => ['nullable', 'numeric'],
            'qty' => ['nullable', 'integer'],
            'weight' => ['nullable', 'numeric'],
            'volume' => ['nullable', 'numeric'],
            'title' => ['nullable', 'string', 'max:95'],
            'sort' => ['required', 'integer'],
            'item_id' => ['nullable', 'integer'], // item relation
        ];
    }

}
