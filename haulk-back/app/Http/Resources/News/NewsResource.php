<?php

namespace App\Http\Resources\News;

use App\Http\Resources\Files\ImageResource;
use App\Models\News\News;
use Config;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="NewsResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                    required={"title_en", "title_ru", "title_es"},
     *                    @OA\Property(property="id", type="integer", description="News id"),
     *                    @OA\Property(property="title", type="string", description="News title"),
     *                    @OA\Property(property="body_short", type="text", description="News body short"),
     *                    @OA\Property(property="body", type="text", description="News body"),
     *                    @OA\Property(property="sticky", type="boolean", description="News sticky"),
     *                    @OA\Property(property="status", type="boolean", description="News status"),
     *                    @OA\Property(property="image", type="object", @OA\Schema(ref="#/components/schemas/Image")),
     *                    @OA\Property(property="created_at", type="integer", description="News creation date"),
     *                )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="NewsResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="News data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"title_en", "title_ru", "title_es"},
     *                    @OA\Property(property="id", type="integer", description="News id"),
     *                    @OA\Property(property="title", type="string", description="News title"),
     *                    @OA\Property(property="body_short", type="text", description="News body short"),
     *                    @OA\Property(property="body", type="text", description="News body"),
     *                    @OA\Property(property="sticky", type="boolean", description="News sticky"),
     *                    @OA\Property(property="status", type="boolean", description="News status"),
     *                    @OA\Property(property="image", type="object", @OA\Schema(ref="#/components/schemas/Image")),
     *                    @OA\Property(property="created_at", type="integer", description="News creation date"),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
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
