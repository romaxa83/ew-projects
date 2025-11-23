<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\OrderDeliveryInformation;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order Delivery Resource",
 *     description="Order Delivery Resource",
 * )
 */
class OrderDeliveryResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var $model OrderDeliveryInformation */
        $model = $this;

        return [
            'id' => $model->id,
            'region_code' => $model->region_code,
            'city_code' => $model->city_code,
            'postal_code' => $model->postal_code,
            'address' => $model->address,
            'tariff_code' => $model->tariff_code,
            'delivery_cost' => $model->delivery_cost,
            'time' => $model->time,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID информации о доставке", example=1),
     * @OA\Property(property="region_code", title="Region code", description="Код области (СДЕК)", example=299),
     * @OA\Property(property="city_code", title="City code", description="Код города (СДЕК)", example=4576),
     * @OA\Property(property="postal_code", title="Postal code", description="Почтовый индекс", example="030000"),
     * @OA\Property(property="address", title="Address", description="Адрес доставки", example="М. Маметовой, 4"),
     * @OA\Property(property="tariff_code", title="Tariff code", description="Код тарифа (СДЕК)", example=139),
     * @OA\Property(property="delivery_cost", title="Delivery cost", description="Стоимость доставки", example=750),
     * @OA\Property(property="time", title="Delivery time", description="Время доставки", example="09:00 - 10:00"),
     */
}
