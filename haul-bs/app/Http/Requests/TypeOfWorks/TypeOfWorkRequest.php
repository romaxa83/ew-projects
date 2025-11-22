<?php

namespace App\Http\Requests\TypeOfWorks;

use App\Dto\TypeOfWorks\TypeOfWorkDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Rules\TypeOfWorks\QuantityRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="TypeOfWorkRequest",
 *     required={"name", "duration", "hourly_rate"},
 *     @OA\Property(property="name", type="string", example="Empty"),
 *     @OA\Property(property="duration", type="string", example="1:40", description="Type Of Work duration"),
 *     @OA\Property(property="hourly_rate", type="nu,ber", example="10.3", description="Type Of Work rate"),
 *     @OA\Property(property="inventories", type="array", @OA\Items(ref="#/components/schemas/InventoryTypeOfWorkRaw"), nullable=true),
 * )
 *
 * @OA\Schema(schema="InventoryTypeOfWorkRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"id", "quantity"},
 *          @OA\Property(property="id", type="string", description="Inventory id", example="1"),
 *          @OA\Property(property="quantity", type="string", description="quantity", example="4"),
 *      )}
 *  )
 */


class TypeOfWorkRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'name' => ['required', 'string'],
            'duration' => ['required', 'string', 'regex:/^\d+\:\d+$/'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'inventories' => ['nullable', 'array', 'max:30'],
            'inventories.*.id' => ['required', 'int', Rule::exists(Inventory::TABLE, 'id')],
            'inventories.*.quantity' => ['required', 'numeric', 'min:0', new QuantityRule($this)],
        ];
    }

    public function dto(): TypeOfWorkDto
    {
        return TypeOfWorkDto::byArgs($this->validated());
    }
}
