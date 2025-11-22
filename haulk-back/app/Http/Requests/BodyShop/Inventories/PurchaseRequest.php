<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Dto\BodyShop\Inventories\PurchaseDto;
use App\Models\BodyShop\Inventories\Inventory;
use App\Rules\BodyShop\Inventory\QuantityRule;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        /** @var Inventory $inventory */
        $inventory = $this->route('inventory');

        $rules = [
            'quantity' => ['required', 'numeric', new QuantityRule($inventory->unit_id)],
            'date' => ['required', 'string', 'date_format:m/d/Y'],
            'cost' => ['required', 'numeric', 'min:0.01'],
            'invoice_number' => ['nullable', 'string', 'max:15'],
        ];

        return $rules;
    }

    public function getDto(): PurchaseDto
    {
        return PurchaseDto::byParams($this->validated());
    }
}
