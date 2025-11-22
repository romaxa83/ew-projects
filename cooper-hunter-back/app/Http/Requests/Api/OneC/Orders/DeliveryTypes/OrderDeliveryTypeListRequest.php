<?php

namespace App\Http\Requests\Api\OneC\Orders\DeliveryTypes;

use App\Http\Requests\BaseFormRequest;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission;

/**
 * @bodyParam id int Filter by ID
 * @bodyParam query string Filter by "title" and/or "description" fields
 * @bodyParam published bool Filter by active(published)
 */
class OrderDeliveryTypeListRequest extends BaseFormRequest
{
    public const PERMISSION = OrderDeliveryTypeListPermission::KEY;

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'int'],
            'query' => ['nullable', 'string'],
            'published' => ['nullable', 'bool'],
        ];
    }
}
