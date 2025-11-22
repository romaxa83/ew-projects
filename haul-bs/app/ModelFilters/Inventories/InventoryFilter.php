<?php

namespace App\ModelFilters\Inventories;

use App\Enums\Inventories\InventoryStockStatus;
use App\Foundations\Models\BaseModelFilter;
use Illuminate\Database\Eloquent\Builder;

class InventoryFilter extends BaseModelFilter
{
    public function search(string $value)
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';

        $this->where(function(Builder $query) use ($searchString){
            return $query->whereRaw('lower(name) like ?', [$searchString])
                ->orWhereRaw('lower(stock_number) like ?', [$searchString])
                ->orWhereRaw('lower(article_number) like ?', [$searchString])
            ;
        });
    }

    public function supplier(int|string $value): void
    {
        $this->where('supplier_id', $value);
    }

    public function category(int|string $value): void
    {
        $this->where('category_id', $value);
    }

    public function brand(int|string $value): void
    {
        $this->where('brand_id', $value);
    }

    public function forShop($value): void
    {
        $value = to_bool($value);

        $this->where('for_shop', $value);
    }

    public function onlyMinLimit($value): void
    {
        $value = to_bool($value);

        if($value){
            $this->whereRaw('quantity <= min_limit');
        }
    }

    public function status(string $value): void
    {
        if (InventoryStockStatus::isOut($value)) {
            $this->where('quantity', '<=', 0);
        } else {
            $this->where('quantity', '>', 0);
        }
    }
}
