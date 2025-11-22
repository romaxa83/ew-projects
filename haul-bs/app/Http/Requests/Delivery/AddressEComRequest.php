<?php

namespace App\Http\Requests\Delivery;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @OA\Schema(type="object", title="AddressEComRequest",
 *     required={"zip", "state", "city"},
 *     @OA\Property(property="zip", type="integer", example="60661"),
 *     @OA\Property(property="address", type="string", example="370 North Desplaines Street"),
 *     @OA\Property(property="state", type="string", example="IL"),
 *     @OA\Property(property="city", type="string", example="Chicago"),
 * )
 */
class AddressEComRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'address' => ['required', 'string', 'max:191'],
            'state' => ['required', 'string', 'max:2'],
            'city' => ['required', 'string', 'max:191'],
        ];
    }
}
