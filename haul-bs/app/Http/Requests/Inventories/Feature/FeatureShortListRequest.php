<?php

namespace App\Http\Requests\Inventories\Feature;

use App\Foundations\Http\Requests\Common\SearchRequest;

class FeatureShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'search' => ['nullable', 'required_without:id', 'string', 'min:2'],
        ]);
    }
}
