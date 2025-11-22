<?php

namespace App\Http\Requests\Locations;

use App\Foundations\Http\Requests\BaseFormRequest;

class CityAutocompleteRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->searchRule(),
            [
                'zip' => ['nullable', 'string', 'min:3'],
                'limit' => ['nullable', 'int']
            ]
        );
    }
}
