<?php

namespace App\Resources\Report;

use App\Models\Report\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ReportVideo Resource",
 *     @OA\Property(property="name", type="string", description="Название видео", example="VID_20200408_152219"),
 *     @OA\Property(property="url", type="string", description="Путь к видео", example="https://api.jd-demonstration.com/storage/video/312/VID_20200408_152219.mp4"),
 *     @OA\Property(property="download", type="string", description="Линк на скачивание", example="http://192.168.144.1/api/report/download-video/1080"),
 * )
 */

class ReportVideoResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Video $video */
        $video = $this;

        return [
            'name' => $video->name,
            'url' => $video->url,
            'download' => url(route('api.download-video', [$video->report_id]))
        ];
    }
}
