<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Add Product to Cart request",
 *     required={"product_id"}
 * )
 */
class AddProductToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'int', 'exists:products,id'],
            'quantity' => ['nullable', 'numeric', 'min:1', 'max:999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => __('cms-orders::site.cart.Product id'),
            'quantity' => __('cms-orders::site.cart.Quantity'),
        ];
    }

    /**
     * @OA\Property(property="product_id", title="Product ID", description="ID товара", example=281)
     * @OA\Property(property="quantity", title="Quantity", description="Количество товаров", example=5)
     */
}
