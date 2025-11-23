<?php

namespace WezomCms\Orders\Http\Resources\V1\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\LocationResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Location Resource",
 *     description="Location Resource",
 * )
 */
class LocationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model LocationResponse */
        $model = $this;

        return [
            'city_code' => $model->getCityCode(),
            'postal_code' => $model->getPostalCode(),
            'country_code' => $model->getCountryCode(),
            'region' => $model->getRegion(),
            'region_code' => $model->getRegionCode(),
            'sub_region' => $model->getSubRegion(),
            'city' => $model->getCity(),
            'address' => $model->getAddress(),
        ];
    }

    /**
     * @OA\Property(property="city_code", title="Код города", description="Код (идентификатор) города", example=4756),
     * @OA\Property(property="postal_code", title="Почтовый индекс", description="Почтовый индекс", example="050031"),
     * @OA\Property(property="country_code", title="Код страны", description="Код страны", example="KZ"),
     * @OA\Property(property="region", title="Регион", description="Название региона", example="Алматинская обл."),
     * @OA\Property(property="region_code", title="Код региона", description="Код региона", example=299),
     * @OA\Property(property="sub_region", title="Район", description="Название района", example="Талгарский район"),
     * @OA\Property(property="city", title="Город", description="Название города", example="Талдыкорган"),
     * @OA\Property(property="address", title="Адрес", description="Адрес пункта выдачи", example="11 микрорайон, пр-т. Алтынсарина, 27"),
     */
}
