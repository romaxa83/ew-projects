<?php

namespace App\Http\Requests\Delivery;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @OA\Schema(type="object", title="RateEComRequest",
 *     required={"inventories", "zip", "state", "city"},
 *     @OA\Property(property="inventories", type="array", @OA\Items(ref="#/components/schemas/InventoryItemRequest"), nullable=false),
 *     @OA\Property(property="zip", type="integer", example="60661"),
 *     @OA\Property(property="address", type="string", example="370 North Desplaines Street"),
 *     @OA\Property(property="state", type="string", example="IL"),
 *     @OA\Property(property="city", type="string", example="Chicago"),
 * ),
 * @OA\Schema(schema="InventoryItemRequest", type="object", allOf={
 *     @OA\Schema(
 *     required={"id", "count"},
 *        @OA\Property(property="id", type="string", example=5),
 *        @OA\Property(property="count", type="string", example=1)
 *     )}
 * )
 */
class RateEComRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'inventories' => ['required', 'array'],
            'inventories.*.id' => ['required', 'exists:inventories,id'],
            'inventories.*.count' => ['required', 'integer', 'min:1'],
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'address' => ['nullable', 'string', 'max:191'],
            'state' => ['required', 'string', 'max:2'],
            'city' => ['required', 'string', 'max:191'],
        ];
    }
}
