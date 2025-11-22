<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;

class OrderAddPackingSlipRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.*.guid' => ['required', 'string'],
            'data.*.status' => ['required', 'string'],
            'data.*.number' => ['required', 'string'],
            'data.*.tracking_number' => ['nullable', 'string'],
            'data.*.tracking_company' => ['nullable', 'string'],
            'data.*.shipped_date' => ['nullable', 'date_format:Y-m-d'],
            'data.*.products' => ['required', 'array'],
            'data.*.products.*.guid' => ['required', 'string'],
            'data.*.products.*.qty' => ['required', 'numeric'],
            'data.*.products.*.description' => ['nullable', 'string'],
            'data.*.dimensions' => ['nullable', 'array'],
            'data.*.dimensions.*.pallet' => ['required', 'integer', 'min:1'],
            'data.*.dimensions.*.box_qty' => ['required', 'integer', 'min:1'],
            'data.*.dimensions.*.type' => ['required', 'string'],
            'data.*.dimensions.*.weight' => ['required', 'numeric'],
            'data.*.dimensions.*.width' => ['required', 'numeric'],
            'data.*.dimensions.*.depth' => ['required', 'numeric'],
            'data.*.dimensions.*.height' => ['required', 'numeric'],
            'data.*.dimensions.*.class_freight' => ['required', 'integer'],
        ];
    }
}
