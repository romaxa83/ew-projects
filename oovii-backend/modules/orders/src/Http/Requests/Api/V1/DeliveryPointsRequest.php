<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryPointsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'city' => 'required|integer',
        ];
    }
}

