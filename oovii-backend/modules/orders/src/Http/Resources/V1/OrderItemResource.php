<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\OrderItem;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order Item Resource",
 *     description="Order Item Resource",
 * )
 */
class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var $item OrderItem */
        $item = $this;

        return [
            'id' => $item->id,
            'product_id' => $item->product->id,
            'name' => $item->product->name,
            'image' => $item->product->getImageUrl(),
            'quantity' => $item->quantity,
            'price' => $item->price,
            'purchase_price' => $item->purchase_price,
            'unit' => $item->unit,
            'total' => $item->whole_purchase_price,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID позиции в заказе", example=1),
     * @OA\Property(property="product_id", title="Product id", description="ID товара", example=184),
     * @OA\Property(property="name", title="Name", description="Название товара", example="Columbia Grand Trek"),
     * @OA\Property(property="image", title="Image", description="Изображение товара", example="/images/123.jpeg"),
     * @OA\Property(property="quantity", title="Quantity", description="Количество товара в заказе", example=3),
     * @OA\Property(property="price", title="Price", description="Цена товара (без скидок)", example=6799),
     * @OA\Property(property="purchase_price", title="Purchase price", description="Цена товара (окончательная)", example=5499),
     * @OA\Property(property="unit", title="Unit", description="Единица измерения", example="шт"),
     * @OA\Property(property="total", title="Total", description="Общая стоимость позиции", example=16497),
     */
}
