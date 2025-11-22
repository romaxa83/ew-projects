<?php

namespace App\Http\Requests\BodyShop\Orders;

use App\Dto\BodyShop\Orders\OrderDto;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Rules\BodyShop\Orders\HasEnoughInventory;
use App\Rules\BodyShop\Orders\InventoryHasPrice;
use App\Rules\BodyShop\Orders\QuantityRule;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $typeOfWorkExistsRule = Rule::exists(TypeOfWork::TABLE_NAME, 'id');
        if ($this->order->id ?? null) {
            $typeOfWorkExistsRule->where('order_id', $this->order->id);
        }

        return  [
            'truck_id' => ['nullable', 'int', Rule::exists(Truck::TABLE_NAME, 'id')],
            'trailer_id' => ['required_without:truck_id', 'integer', Rule::exists(Trailer::TABLE_NAME, 'id')],
            'discount' => ['nullable', 'numeric', 'between:0,100'],
            'tax_inventory' => ['nullable', 'numeric', 'min:0'],
            'tax_labor' => ['nullable', 'numeric', 'min:0'],
            'implementation_date' => ['required', 'string', 'date'],
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists(User::TABLE_NAME, 'id'),
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || $user->getRoleName() !== User::BSMECHANIC_ROLE) {
                        $fail(trans('Mechanic not found.'));
                    }
                }
            ],
            'notes' => ['nullable', 'string'],
            'types_of_work' => ['required', 'array', 'min:1'],
            'types_of_work.*.id' => [
                'nullable',
                'int',
                $typeOfWorkExistsRule,
            ],
            'types_of_work.*.name' => ['required', 'string'],
            'types_of_work.*.save_to_the_list' => ['nullable', 'bool'],
            'types_of_work.*.duration' => ['required', 'string', 'regex:/^\d+\:\d+$/'],
            'types_of_work.*.hourly_rate' => ['required', 'numeric', 'min:0'],
            'types_of_work.*.inventories' => ['nullable', 'array'],
            'types_of_work.*.inventories.*.id' => [
                'required',
                'integer',
                Rule::exists(Inventory::TABLE_NAME, 'id'),
                new InventoryHasPrice(),
            ],
            'types_of_work.*.inventories.*.quantity' => [
                'required',
                'numeric',
                'min:0',
                new QuantityRule($this),
                new HasEnoughInventory($this),
            ],
            Order::ATTACHMENT_FIELD_NAME => ['nullable', 'array'],
            Order::ATTACHMENT_FIELD_NAME . '.*' => ['file', $this->orderAttachmentTypes()],
            'due_date' => ['required', 'string', 'date'],
            'need_to_update_prices' => ['nullable', 'boolean'],
        ];
    }

    public function dto(): OrderDto
    {
        return OrderDto::byParams($this->validated());
    }

    public function orderAttachmentTypes(): string
    {
        return 'mimes:pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx';
    }
}
