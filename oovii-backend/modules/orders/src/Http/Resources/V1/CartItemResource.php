<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Http\Resources\V1\ProductSimpleResource;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Contracts\CartItemInterface;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Cart Item Resource",
 *     description="Cart Item Resource",
 * )
 */
class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var CartItemInterface $this */
        /** @var Product $product */
        $product = $this->getPurchaseItem();

        return [
            'row_id' => $this->getUniqueId(),
            'product' => ProductSimpleResource::make($product),
            'quantity' => [
                'value' => $this->getQuantity(),
                'min' => $product->minCountForPurchase(),
                'step' => $product->stepForPurchase(),
            ],
            'sub_total' => $this->getSubTotal(),
            'crossed_out_sub_total' => $this->crossedOutSubTotal(),
            'total' => $this->getTotal(),
        ];
    }

    /**
     * @OA\Property(property="row_id", title="Cart Item ID", description="Идентификатор позиции в корзине", example="09f29efdfb0bdc363c452af3432d8ff1"),
     * @OA\Property(property="product", title="Product", description="Товар", type="object",
     *     ref="#/components/schemas/ProductSimpleResource")
     * )
     * @OA\Property(property="quantity", title="Quantity", type="object",
     *     @OA\Property(property="value", title="Value", description="Количество единиц товара", example=5),
     *     @OA\Property(property="min", title="Min", description="Минимальное кол-во товара в заказе", example=1),
     *     @OA\Property(property="step", title="Step", description="Шаг для добавления товара в корзину", example=1),
     * ),
     * @OA\Property(property="sub_total", title="Subtotal", description="Общая сумма товара в позиции, без учета скидок", example=625),
     * @OA\Property(property="crossed_out_sub_total", title="Crossed Out Sub Total", description="Общая стоимость товара в позиции, из расчета по 'старой цене'", example=700),
     * @OA\Property(property="total", title="Total", description="Общая сумма товара в позиции, со всеми скидками", example=584),
     */
}
