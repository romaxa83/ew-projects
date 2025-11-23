<?php

namespace WezomCms\Orders\Http\Resources\V1\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\CitiesResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="City Resource",
 *     description="City Resource",
 * )
 */
class CityResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model CitiesResponse */
        $model = $this;

        return [
            'code' => $model->getCode(),
            'country_code' => $model->getCountryCode(),
            'region_code' => $model->getRegionCode(),
            'region' => $model->getRegion(),
            'sub_region' => $model->getSubRegion(),
            'city' => $model->getCity(),
        ];
    }

    /**
     * @OA\Property(property="code", title="Код города", description="Код (идентификатор) города", example=11490),
     * @OA\Property(property="country_code", title="Код страны", description="Код страны", example="KZ"),
     * @OA\Property(property="region_code", title="Код региона", description="Код региона", example=299),
     * @OA\Property(property="region", title="Регион", description="Название региона", example="Алматинская обл."),
     * @OA\Property(property="sub_region", title="Район", description="Название района", example="Талгарский район"),
     * @OA\Property(property="city", title="Город", description="Название города", example="Талдыкорган"),
     */
}
