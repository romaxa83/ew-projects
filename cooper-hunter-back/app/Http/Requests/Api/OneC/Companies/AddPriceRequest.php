<?php

namespace App\Http\Requests\Api\OneC\Companies;

use App\Http\Requests\BaseFormRequest;

class AddPriceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.*.product_guid' => ['required', 'string'],
            'data.*.price' => ['required', 'numeric'],
            'data.*.description' => ['nullable', 'string'],
        ];
    }
}

