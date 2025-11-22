<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Http\Requests\BaseFormRequest;

class FeaturesListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'title' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
