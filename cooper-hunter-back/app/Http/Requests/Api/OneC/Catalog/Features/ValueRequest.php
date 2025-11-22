<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Dto\Catalog\ValueDto;
use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use Illuminate\Validation\Rule;

class ValueRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'feature_guid' => ['required', 'uuid', Rule::exists(Feature::class, 'guid')],
            'title' => [
                'required',
                'string',
                Rule::unique(Value::class, 'title')
            ],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function getDto(): ValueDto
    {
        return ValueDto::byArgs(
            array_merge(
                $this->validated(),
                [
                    'feature_id' => Feature::where('guid', $this->get('feature_guid'))->firstOrFail()->id
                ]
            )
        );
    }
}
