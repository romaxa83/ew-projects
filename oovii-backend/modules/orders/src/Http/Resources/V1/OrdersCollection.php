<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Orders Collection",
 *     description="Order Collection Resource",
 * )
 */
class OrdersCollection extends ResourceCollection
{
    public static $wrap = 'orders';

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return OrderResource::collection($this->collection)->toArray($request);
    }

    /**
     * @OA\Property(property="orders", title="Orders", description="Список заказов", type="array",
     *     @OA\Items(ref="#/components/schemas/OrderResource")
     * ),
     * @OA\Property(property="links", title="Pagination links", type="object", ref="#/components/schemas/PaginationLinks"),
     * @OA\Property(property="meta", title="Pagination meta", type="object", ref="#/components/schemas/PaginationMeta"),
     */
}
