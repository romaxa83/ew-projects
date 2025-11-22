<?php

namespace App\Http\Requests\Inventories\Category;

use App\Foundations\Http\Requests\BaseFormRequest;

class CategoryFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->searchRule(),
            []
        );
    }
}
