<?php

namespace App\Rules\BodyShop\Orders;

use App\Http\Requests\BodyShop\Orders\OrderRequest;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\Models\Media;

class HasEnoughInventory implements Rule
{
    private OrderRequest $request;

    /**
     * Create a new rule instance.
     * @param OrderRequest $request
     * @return void
     */
    public function __construct(OrderRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Media|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $inventoryAttr = str_replace('quantity', 'id', $attribute);
        $inventoryId = (int) Arr::get($this->request->toArray(), $inventoryAttr);
        $inventory = Inventory::find($inventoryId);

        if (!$inventory) {
            return false;
        }

        $updatedInventoriesCount = 0;

        foreach ($this->request->types_of_work as $typeOfWork) {
            $inventories = $typeOfWork['inventories'] ?? [];
            foreach ($inventories as $inventoryData) {
                if ((int)$inventoryData['id'] === $inventoryId) {
                    $updatedInventoriesCount += $inventoryData['quantity'];
                }
            }
        }

        $orderInventoriesCount = 0;
        if ($this->request->order instanceof Order) {
            $orderInventories = $this->request->order->inventories()
                ->where('inventory_id', $inventoryId)
                ->get();

            foreach ($orderInventories as $orderInventory) {
                $orderInventoriesCount += $orderInventory->quantity;
            }
        }

        $totalInventoryQuantity = $updatedInventoriesCount - $orderInventoriesCount;

        return $inventory->quantity >= $totalInventoryQuantity;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('The required quantity is not in stock.');
    }
}
