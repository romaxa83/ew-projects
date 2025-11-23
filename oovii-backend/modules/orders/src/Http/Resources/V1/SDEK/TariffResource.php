<?php

namespace WezomCms\Orders\Http\Resources\V1\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\TariffListResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Tariff resource",
 *     description="Tariff resource",
 * )
 */
class TariffResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model TariffListResponse */
        $model = $this;

        return [
            'tariff_name' => $model->getTariffName(),
            'tariff_code' => $model->getTariffCode(),
            'delivery_sum' => $model->getDeliverySum(),
            'period_min' => $model->getPeriodMin(),
            'period_max' => $model->getPeriodMax(),
        ];
    }

    /**
     * @OA\Property(property="tariff_name", title="Название тарифа", description="Название тарифа", example="Доставка курьером"),
     * @OA\Property(property="tariff_code", title="Код тарифа", description="Код тарифа", example=139),
     * @OA\Property(property="delivery_sum", title="Стоимость доставки", description="Общая стоимость доставки", example=10500),
     * @OA\Property(property="period_min", title="Минимальный срок доставки", description="Минимальный срок доставки (дни)", type="object", example=3),
     * @OA\Property(property="period_max", title="Максимальный срок доставки", description="Максимальный срок доставки (дни)", example=5),
     */
}
