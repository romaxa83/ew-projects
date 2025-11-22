<?php

namespace App\Http\Requests\Inventories\Inventory;

use App\Enums\Inventories\InventoryStockStatus;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Suppliers\Supplier;
use Illuminate\Validation\Rule;

class InventoryFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {

        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            [
                'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE, 'id')],
                'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE, 'id')],
                'brand_id' => ['nullable', 'integer', Rule::exists(Brand::TABLE, 'id')],
                'status' => ['nullable', 'string', EnumHelper::ruleIn(InventoryStockStatus::class)],
                'only_min_limit' => ['nullable'],
                'for_shop' => ['nullable'],
            ]
        );
    }
}
