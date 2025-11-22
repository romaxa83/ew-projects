<?php

namespace App\Http\Requests\Api\OneC\Commercial;

use App\Http\Requests\BaseFormRequest;

class TaxRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.*.guid' => ['required', 'string'],
            'data.*.name' => ['required', 'string'],
            'data.*.value' => ['required', 'numeric'],
        ];
    }
}
