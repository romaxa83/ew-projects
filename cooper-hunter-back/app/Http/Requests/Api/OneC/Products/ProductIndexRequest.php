<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Catalog\Products\ListPermission;
use JetBrains\PhpStorm\Pure;

class ProductIndexRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(ListPermission::KEY);
    }

    #[Pure] public function rules(): array
    {
        return $this->getPaginationRules();
    }
}
