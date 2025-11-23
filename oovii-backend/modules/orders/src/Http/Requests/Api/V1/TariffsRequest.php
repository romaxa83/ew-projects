<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request get tariffs list",
 *     required={"city_code"}
 * )
 */
class TariffsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'city_code' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:6',
        ];
    }

    public function attributes(): array
    {
        return [
            'city_code' => __('cms-orders::site.checkout.Locality'),
            'postal_code' => __('cms-orders::site.checkout.Postal code'),
        ];
    }

    /**
     * @OA\Property(property="city_code", title="City code", description="Код города получателя", example="12345")
     * @OA\Property(property="postal_code", title="Postal code", description="Почтовый индекс получателя", type="string", example="123456")
     */
}

