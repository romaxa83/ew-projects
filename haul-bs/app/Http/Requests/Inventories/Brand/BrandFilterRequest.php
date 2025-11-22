<?php

namespace App\Http\Requests\Inventories\Brand;

use App\Foundations\Http\Requests\BaseFormRequest;

class BrandFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
        );
    }
}
