<?php

namespace App\Http\Requests\Inventories\FeatureValue;

use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Models\Inventories\Features\Feature;
use Illuminate\Validation\Rule;

class FeatureValueShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'feature_id' => ['nullable', 'integer', Rule::exists(Feature::TABLE, 'id')],
            ]
        );
    }
}
