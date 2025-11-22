<?php

namespace App\Http\Requests\Vehicles;

use App\Foundations\Http\Requests\BaseFormRequest;

class ModelListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->searchRule(),
            [
                'make_name' => ['nullable', 'string']
            ]
        );
    }
}
