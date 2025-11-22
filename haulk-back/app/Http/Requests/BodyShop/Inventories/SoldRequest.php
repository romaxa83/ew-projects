<?php

namespace App\Http\Requests\BodyShop\Inventories;

use App\Dto\BodyShop\Inventories\SoldDto;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Rules\BodyShop\Inventory\QuantityRule;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SoldRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        /** @var Inventory $inventory */
        $inventory = $this->route('inventory');

        return [
            'quantity' => ['required', 'numeric', new QuantityRule($inventory->unit_id), 'max:' . $inventory->quantity],
            'date' => ['required', 'string', 'date_format:m/d/Y'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'describe' => ['required', 'string', Rule::in([
                Transaction::DESCRIBE_SOLD,
                Transaction::DESCRIBE_BROKE,
                Transaction::DESCRIBE_DEFECT,
            ])],
            'discount' => ['nullable', 'numeric', 'min:0.01'],
            'tax' => ['nullable', 'numeric', 'min:0.01'],
            'invoice_number' => ['required_if:describe,sold', 'string', 'max:15'],
            'payment_date' => ['required_if:describe,sold', 'string', 'date_format:m/d/Y'],
            'payment_method' => [
                'required_if:describe,sold',
                'string',
                Rule::in(array_keys(Transaction::PAYMENT_METHODS))
            ],
            'first_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== Transaction::DESCRIBE_SOLD) {
                    return false;
                }

                if (!empty($this->request->get('company_name'))) {
                    return false;
                }

                return true;
            })],
            'last_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== Transaction::DESCRIBE_SOLD) {
                    return false;
                }

                if (!empty($this->request->get('company_name'))) {
                    return false;
                }

                return true;
            })],
            'company_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== Transaction::DESCRIBE_SOLD) {
                    return false;
                }

                if (!empty($this->request->get('first_name')) && !empty($this->request->get('last_name'))) {
                    return false;
                }

                return true;
            })],
            'phone' => ['required_if:describe,sold', 'string', $this->USAPhone()],
            'email' => ['required_if:describe,sold', 'string', 'email'],
        ];
    }

    public function getDto(): SoldDto
    {
        return SoldDto::byParams($this->validated());
    }
}
