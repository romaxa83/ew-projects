<?php

namespace App\Http\Resources\News;

use App\Http\Resources\Files\ImageResource;
use App\Models\News\News;
use Config;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsPaginatedResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="NewsPaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        description="News paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/NewsResourceRaw")
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var News $this*/
        return [
            'id' => (int) $this->id,
            'title' => $this->getTitle(Config::get('app.locale')),
            'body_short' => $this->getBodyShort(Config::get('app.locale')),
            'body' => $this->getBody(Config::get('app.locale')),
            'sticky' => (bool) $this->sticky,
            'status' => (bool) $this->status,
            'image' => ImageResource::make($this->getFirstMedia(News::NEWS_PHOTO_COLLECTION_NAME)),
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
