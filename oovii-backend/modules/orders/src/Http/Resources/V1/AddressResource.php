<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\UserAddress;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Address Resource",
 *     description="Address Resource",
 * )
 */
class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model UserAddress */
        $model = $this;

        return [
            'id' => $model->id,
            'region_code' => (int)$model->region_code,
            'region_name' => $model->region,
            'city_code' => (int)$model->city_code,
            'city_name' => $model->city,
            'postal_code' => $model->postal_code,
            'address' => $model->address,
            'name' => $model->name,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID адреса", example=1),
     * @OA\Property(property="name", title="Name", description="Название адреса", example="Дом"),
     * @OA\Property(property="region_code", title="Region code", description="Код области в СДЕК", example=299),
     * @OA\Property(property="region_name", title="Region name", description="Название области", example="Алматинская область"),
     * @OA\Property(property="city_code", title="City code", description="Код города в СДЕК", example=10544),
     * @OA\Property(property="city_name", title="City name", description="Название города", example="Алматы"),
     * @OA\Property(property="postal_code", title="Postal code", description="Почтовый индекс", example="030000"),
     * @OA\Property(property="address", title="Address", description="Адресс доставки", example="М. Маметовой, 4"),
     */
}
