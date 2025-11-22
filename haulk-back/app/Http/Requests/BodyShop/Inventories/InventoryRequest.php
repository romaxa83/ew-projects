<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Dto\BodyShop\Inventories\InventoryDto;
use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Unit;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Rules\BodyShop\Inventory\QuantityRule;
use App\Services\Utilities\RulesIdentifyWithArrayService;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        /** @var Inventory $inventory */
        $inventory = $this->route('inventory');

        $stockNumberUniqueRule = Rule::unique(Inventory::TABLE_NAME, 'stock_number');
        if ($inventory) {
            $stockNumberUniqueRule->ignore($inventory->id);
        }

        $rules = [
            'name' => ['required', 'string'],
            'stock_number' => ['required', 'string', 'alpha_dash', $stockNumberUniqueRule],
            'category_id' => ['nullable', 'integer', Rule::exists(Category::TABLE_NAME, 'id')],
            'price_retail' => ['nullable', 'numeric', 'min:0.01'],
            'unit_id' => ['required', 'integer', Rule::exists(Unit::TABLE_NAME, 'id')],
            'min_limit' => ['nullable', 'numeric', 'min:0', new QuantityRule($this->unit_id ?? null)],
            'supplier_id' => ['nullable', 'integer', Rule::exists(Supplier::TABLE_NAME, 'id')],
            'notes' => ['nullable', 'string'],
            'for_sale' => ['nullable', 'boolean'],
            'length' => ['nullable', 'numeric', 'min:0.01'],
            'width' => ['nullable', 'numeric', 'min:0.01'],
            'height' => ['nullable', 'numeric', 'min:0.01'],
            'weight' => ['nullable', 'numeric', 'min:0.01'],
            'min_limit_price' => ['nullable', 'numeric', 'min:0.01'],
        ];

        if (!$inventory) {
            $rules = array_merge(
                $rules,
                [
                    'purchase' => ['required', 'array'],
                    'purchase.quantity' => ['required', 'numeric', new QuantityRule($this->unit_id ?? null)],
                    'purchase.date' => ['required', 'string', 'date_format:m/d/Y'],
                    'purchase.cost' => ['required', 'numeric', 'min:0.01'],
                    'purchase.invoice_number' => ['nullable', 'string', 'max:15'],
                ]
            );
        }

        return $rules;
    }

    public function getDto(): InventoryDto
    {
        return InventoryDto::byParams($this->validated());
    }

    public function rulesForOnlyReceived(): array
    {
        return resolve(RulesIdentifyWithArrayService::class)->identify($this->rules(), $this->all());
    }
}
