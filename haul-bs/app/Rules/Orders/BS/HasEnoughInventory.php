<?php

namespace App\Rules\Orders\BS;

use App\Http\Requests\Orders\BS\OrderRequest;
use App\Models\Inventories\Inventory;
use App\Models\Orders\BS\Order;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class HasEnoughInventory implements Rule
{
    private OrderRequest $request;

    public function __construct(OrderRequest $request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value): bool
    {
        $inventoryAttr = str_replace('quantity', 'id', $attribute);
        $inventoryId = (int)Arr::get($this->request->toArray(), $inventoryAttr);
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

    public function message(): string
    {
        return trans('The required quantity is not in stock.');
    }
}
