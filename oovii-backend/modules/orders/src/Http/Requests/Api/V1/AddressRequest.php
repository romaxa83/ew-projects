<?php

namespace WezomCms\Orders\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Orders\Rules\SdekCity;
use WezomCms\Orders\Rules\SdekRegion;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Add user address request",
 *     required={"region_code", "city_code", "name", "address"}
 * )
 */
class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'region_code' => ['required', 'int', new SdekRegion()],
            'city_code' => ['required', 'int', new SdekCity((int) $this->get('region_code'))],
            'name' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:6'],
            'address' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'region_code' => __('cms-orders::site.address.Region code'),
            'city_code' => __('cms-orders::site.address.City code'),
            'name' => __('cms-orders::site.address.Name'),
            'postal_code' => __('cms-orders::site.address.Postal code'),
            'address' => __('cms-orders::site.address.Address'),
        ];
    }

    /**
     * @OA\Property(property="region_code", title="Region", description="Идентификатор области", example=299),
     * @OA\Property(property="city_code", title="City", description="Идентификатор города", example=11490),
     * @OA\Property(property="name", title="Name", description="Название адреса", example="Дом"),
     * @OA\Property(property="postal_code", title="Postal code", description="Почтовый индекс", type="string", example="123456"),
     * @OA\Property(property="address", title="Address", description="Адрес доставки", example="ул. Какая-то, 23, кв. 12"),
     */
}
