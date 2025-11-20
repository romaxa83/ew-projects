<?php

namespace App\Resources\Report;

use App\Models\Image;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportImageResource",
 *     @OA\Property(property="module", type="string", description="Модуль", example="working_hours_at_the_beg"),
 *     @OA\Property(property="basename", type="string", description="Базовое имя", example="bender.jpg"),
 *     @OA\Property(property="url", type="string", description="Путь к картинке", example="http://192.168.144.1/storage/report/908/bender.jpg"),
 *     @OA\Property(property="photo_created", type="string", description="Дата создание фото", example="2021:07:25 10:46:07"),
 *     @OA\Property(property="coords", type="object",
 *         @OA\Property(property="lat", type="string", description="Широта", example="47.36743925"),
 *         @OA\Property(property="lon", type="string", description="Долгота", example="33.220668777778"),
 *     ),
 * )
 */

class ReportImageResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Image $image */
        $image = $this;

        return [
            'module' => $image->model,
            'basename' => $image->basename,
            'url' => Image::getUrl($image->url),
            'photo_created' => $image->photo_created_at,
            'coords' => $image->getCoords()
        ];
    }
}
