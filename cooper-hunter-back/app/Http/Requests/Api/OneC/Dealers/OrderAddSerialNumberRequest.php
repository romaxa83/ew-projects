<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;

class OrderAddSerialNumberRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.*.guid' => ['required', 'string'],
            'data.*.serial_numbers' => ['required', 'array'],
        ];
    }
}


