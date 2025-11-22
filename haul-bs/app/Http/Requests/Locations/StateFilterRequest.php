<?php

namespace App\Http\Requests\Locations;

use App\Foundations\Http\Requests\BaseFormRequest;

class StateFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:3'],
        ];
    }
}
