<?php

namespace App\ModelFilters\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use EloquentFilter\ModelFilter;

class InventoryFilter extends ModelFilter
{
    public function q(string $name): void
    {
        $searchString = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';

        $this->whereRaw('lower(name) like ?', [$searchString])
            ->orWhereRaw('lower(stock_number) like ?', [$searchString]);
    }

    public function category(int $categoryId): void
    {
        $this->where('category_id', $categoryId);
    }

    public function status(string $status): void
    {
        if ($status === Inventory::STATUS_OUT_OF_STOCK) {
            $this->where('quantity', '<=', 0);
        } else {
            $this->where('quantity', '>', 0);
        }
    }

    public function onlyMinLimit($value): void
    {
        $value = to_bool($value);

        if($value){
            $this->whereRaw('quantity <= min_limit');
        }
    }

    public function forSale($value): void
    {
        $value = to_bool($value);

        $this->where('for_sale', $value);
    }

    public function supplier(int $supplierId): void
    {
        $this->where('supplier_id', $supplierId);
    }

    public function searchid(int $id): void
    {
        $this->where('id', $id);
    }
}
