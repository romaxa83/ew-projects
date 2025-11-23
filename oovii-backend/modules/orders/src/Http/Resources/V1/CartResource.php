<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Contracts\CartInterface;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Cart Resource",
 *     description="Cart Resource",
 * )
 */
class CartResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var CartInterface $this */

        return [
            'hash' => $this->getMainCart()->hash,
            'items' => CartItemResource::collection($this->content()->values()),
            'total' => $this->total(),
            'sub_total' => $this->subTotal(),
            'items_quantity' => $this->quantity(),
        ];
    }

    /**
     * @OA\Property(property="hash", title="Cart identifier", description="Hash корзины", example="d1e03c3ac942bbfd243a3463392b916d7f2bd402"),
     * @OA\Property(property="total", title="Total", description="Общая сумма товаров в корзине", example=587),
     * @OA\Property(property="sub_total", title="Subtotal", description="Общая сумма товаров в корзине, без учета скидок", example=625),
     * @OA\Property(property="items_quantity", title="Items quantity", description="Количество товаров в корзине", example=5),
     * @OA\Property(property="items", title="Items", description="Товарные позиции в корзине", type="array",
     *     @OA\Items(ref="#/components/schemas/CartItemResource"))
     * )
     */
}
