<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\Value;
use Illuminate\Validation\Rule;

class ValueUpdateRequest extends ValueRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'title' => [
                    'required',
                    'string',
                    Rule::unique(Value::class, 'title')
                        ->ignore($this->value)
                ],
            ]
        );
    }
}
