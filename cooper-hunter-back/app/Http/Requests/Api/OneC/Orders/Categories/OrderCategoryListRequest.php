<?php

namespace App\Http\Requests\Api\OneC\Orders\Categories;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Orders\Categories\OrderCategoryListPermission;

/**
 * @bodyParam published bool Default true
 */
class OrderCategoryListRequest extends BaseFormRequest
{
    public const PERMISSION = OrderCategoryListPermission::KEY;

    public function rules(): array
    {
        return [
            'published' => ['nullable', 'bool'],
        ];
    }
}
