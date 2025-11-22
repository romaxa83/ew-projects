<?php

namespace App\Http\Requests\Inventories\Feature;

use App\Foundations\Http\Requests\BaseFormRequest;

class FeatureFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
        );
    }
}
