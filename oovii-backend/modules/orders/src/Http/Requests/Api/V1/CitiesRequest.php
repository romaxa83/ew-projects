<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CitiesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'region' => 'required|integer',
            'query' => 'nullable|string',
            'limit' => 'nullable|integer',
        ];
    }
}

