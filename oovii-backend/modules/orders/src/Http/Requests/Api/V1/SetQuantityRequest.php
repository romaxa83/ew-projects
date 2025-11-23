<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Set Cart item quantity request",
 *     required={"unique_id", "quantity"}
 * )
 */
class SetQuantityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'unique_id' => ['required', 'string', 'exists:cart_items,unique_id'],
            'quantity' => ['required', 'numeric', 'min:1', 'max:999999'],
        ];
    }

    /**
     * @OA\Property(property="unique_id", title="Cart Item ID", description="ID товарной позиции в корзине", example="90f33c60258c63f6151367d682fca6f3")
     * @OA\Property(property="quantity", title="Quantity", description="Количество товаров", example=5)
     */
}
