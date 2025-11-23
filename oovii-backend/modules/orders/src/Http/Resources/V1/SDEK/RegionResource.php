<?php

namespace WezomCms\Orders\Http\Resources\V1\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\RegionsResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Region Resource",
 *     description="Region Resource",
 * )
 */
class RegionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model RegionsResponse */
        $model = $this;

        return [
            'country' => $model->getCountry(),
            'country_code' => $model->getCountryCode(),
            'region' => $model->getRegion(),
            'region_code' => $model->getRegionCode(),
        ];
    }

    /**
     * @OA\Property(property="country", title="Страна", description="Название страны", example="Казахстан"),
     * @OA\Property(property="country_code", title="Код страны", description="Код страны", example="KZ"),
     * @OA\Property(property="region", title="Регион", description="Название региона", example="Южно-Казахстанская обл."),
     * @OA\Property(property="region_code", title="Код региона", description="Код региона", example=89),
     */
}
