<?php

namespace App\Http\Requests\Inventories\FeatureValue;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Inventories\Features\Feature;
use Illuminate\Validation\Rule;

class FeatureValueFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            [
                'feature_id' => ['nullable', 'integer', Rule::exists(Feature::TABLE, 'id')]
            ]
        );
    }
}
