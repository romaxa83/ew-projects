<?php

namespace App\Http\Requests\Inventories\Inventory;

use App\Foundations\Http\Requests\Common\SearchRequest;

class InventoryShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'without_ids' => ['nullable', 'array'],
            'without_ids.*' => ['required', 'integer'],
        ]);
    }
}
