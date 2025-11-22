<?php

namespace App\Http\Requests\Orders\Parts;

use App\Dto\Orders\Parts\ItemDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Rules\Orders\Parts\InventoryQuantity;
use App\Rules\Orders\Parts\PriceWithDiscountLessMinPrice;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderPartsItemRequest",
 *     required={"inventory_id", "quantity"},
 *     @OA\Property(property="inventory_id", type="integer", description="Inventory id", example="1"),
 *     @OA\Property(property="quantity", type="number", description="quantity", example="4"),
 *     @OA\Property(property="discount", type="number", description="discount", example="4"),
 * )
 */

class OrderPartsItemRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'inventory_id' => ['required', 'int', Rule::exists(Inventory::TABLE, 'id')],
            'quantity' => ['required', 'numeric', 'min:0', new InventoryQuantity($this)],
            'discount' => ['nullable', 'numeric', new PriceWithDiscountLessMinPrice($this)],
        ];
    }

    public function getDto(): ItemDto
    {
        return ItemDto::byArgs($this->validated());
    }
}
