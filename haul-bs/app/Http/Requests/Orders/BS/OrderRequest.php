<?php

namespace App\Http\Requests\Orders\BS;

use App\Dto\Orders\BS\OrderDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Rules\Orders\BS\HasEnoughInventory;
use App\Rules\Orders\BS\InventoryHasPrice;
use App\Rules\Orders\BS\QuantityRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderRequest",
 *     required={"truck_id", "mechanic_id", "implementation_date", "due_date", "types_of_work"},
 *     @OA\Property(property="truck_id", type="integer", example="13", description="Truck id, required if Trailer id is not specified"),
 *     @OA\Property(property="trailer_id", type="integer", example="3", description="Trailer id, required if Truck id is not specified"),
 *     @OA\Property(property="discount", type="number", example="3.5", description="Order discount, %"),
 *     @OA\Property(property="tax_labor", type="number", example="0.5", description="Orer tax labor, %"),
 *     @OA\Property(property="tax_inventory", type="number", example="2", description="Order tax inventory, %"),
 *     @OA\Property(property="implementation_date", type="string", example="2023-02-13 10:00", description="Order implementation date"),
 *     @OA\Property(property="due_date", type="string", example="2023-02-13", description="Order due date"),
 *     @OA\Property(property="notes", type="string", example="some notes", description="Order notes"),
 *     @OA\Property(property="mechanic_id", type="integer", example="10", description="Order mechanic id"),
 *     @OA\Property(property="types_of_work", type="array",
 *         @OA\Items(ref="#/components/schemas/OrderTypeOfWorkRaw")
 *     ),
 *     @OA\Property(property="attachment_files", type="array", description="attachment files for order",
 *         @OA\Items(type="file")
 *     ),
 * )
 *
 * @OA\Schema(schema="OrderTypeOfWorkRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"name", "duration", "hourly_rate"},
 *          @OA\Property(property="id", type="integer", example="1", nullable=true),
 *          @OA\Property(property="save_to_the_list", type="boolean", example="true", description="Save to common type of work", nullable=true),
 *          @OA\Property(property="name", type="string", example="Empty"),
 *          @OA\Property(property="duration", type="string", example="1:40", description="Type Of Work duration"),
 *          @OA\Property(property="hourly_rate", type="nu,ber", example="10.3", description="Type Of Work rate"),
 *          @OA\Property(property="inventories", type="array",
 *              @OA\Items(ref="#/components/schemas/OrderInventoryTypeOfWorkRaw")
 *          )
 *      )
 * })
 *
 * @OA\Schema(schema="OrderInventoryTypeOfWorkRaw", type="object", allOf={
 *       @OA\Schema(
 *           required={"id", "quantity"},
 *           @OA\Property(property="id", type="integer", description="Inventory id", example="1"),
 *           @OA\Property(property="quantity", type="number", description="quantity", example="4"),
 *       )
 * })
 */

class OrderRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $typeOfWorkExistsRule = Rule::exists(TypeOfWork::TABLE, 'id');
        if ($this->order->id ?? null) {
            $typeOfWorkExistsRule->where('order_id', $this->order->id);
        }

        return  [
            'truck_id' => ['nullable', 'int', Rule::exists(Truck::TABLE, 'id')],
            'trailer_id' => ['required_without:truck_id', 'integer', Rule::exists(Trailer::TABLE, 'id')],
            'discount' => ['nullable', 'numeric', 'between:0,100'],
            'tax_inventory' => ['nullable', 'numeric', 'min:0'],
            'tax_labor' => ['nullable', 'numeric', 'min:0'],
            'implementation_date' => ['required', 'string', 'date'],
            'mechanic_id' => ['required', 'integer',
                Rule::exists(User::TABLE, 'id'),
                function ($attribute, $value, $fail) {
                    /** @var $user User */
                    $user = User::find($value);
                    if (!$user || !$user->role->isMechanic()) {
                        $fail(__('validation.custom.user.role.mechanic_not_found'));
                    }
                }
            ],
            'notes' => ['nullable', 'string'],
            'due_date' => ['required', 'string', 'date'],
            'need_to_update_prices' => ['nullable', 'boolean'],
            'types_of_work' => ['required', 'array', 'min:1'],
            'types_of_work.*.id' => ['nullable', 'int', $typeOfWorkExistsRule,],
            'types_of_work.*.name' => ['required', 'string'],
            'types_of_work.*.save_to_the_list' => ['nullable', 'bool'],
            'types_of_work.*.duration' => ['required', 'string', 'regex:/^\d+\:\d+$/'],
            'types_of_work.*.hourly_rate' => ['required', 'numeric', 'min:0'],
            'types_of_work.*.inventories' => ['nullable', 'array'],
            'types_of_work.*.inventories.*.id' => ['required', 'integer',
                Rule::exists(Inventory::TABLE, 'id'),
                new InventoryHasPrice(),
            ],
            'types_of_work.*.inventories.*.quantity' => ['required', 'numeric', 'min:0',
                new QuantityRule($this),
                new HasEnoughInventory($this),
            ],
            Order::ATTACHMENT_FIELD_NAME => ['nullable', 'array'],
            Order::ATTACHMENT_FIELD_NAME . '.*' => ['file', $this->attachmentMimes()],
        ];
    }

    public function getDto(): OrderDto
    {
        return OrderDto::byArgs($this->validated());
    }
}
