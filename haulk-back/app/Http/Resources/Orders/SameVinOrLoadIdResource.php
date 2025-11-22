<?php

namespace App\Http\Resources\Orders;

use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @mixin Order
 */
class SameVinOrLoadIdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="SameVinOrLoadIdResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="order_id", type="integer", description=""),
     *                @OA\Property(property="load_id", type="string", description=""),
     *            )
     *        }
     *     )
     *    ),
     * )
     *
     */
    public function toArray($request): array
    {
        return [
            'order_id' => $this->id,
            'load_id' => $this->load_id,
        ];
    }
}
