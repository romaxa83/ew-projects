<?php

namespace App\Http\Resources\News;

use App\Http\Resources\Files\ImageResource;
use App\Models\News\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResourceFull extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="NewsResourceFull",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="News data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"title_en", "title_ru", "title_es"},
     *                    @OA\Property(property="id", type="integer", description="News id"),
     *                    @OA\Property(property="title_en", type="string", description="News title_en"),
     *                    @OA\Property(property="title_ru", type="string", description="News title_ru"),
     *                    @OA\Property(property="title_es", type="string", description="News title_es"),
     *                    @OA\Property(property="body_short_en", type="text", description="News body_short_en"),
     *                    @OA\Property(property="body_short_ru", type="text", description="News body_short_ru"),
     *                    @OA\Property(property="body_short_es", type="text", description="News body_short_es"),
     *                    @OA\Property(property="body_en", type="text", description="News body_en"),
     *                    @OA\Property(property="body_ru", type="text", description="News body_ru"),
     *                    @OA\Property(property="body_es", type="text", description="News body_es"),
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
            'title_en' => $this->title_en,
            'title_ru' => $this->title_ru,
            'title_es' => $this->title_es,
            'body_short_en' => $this->body_short_en,
            'body_short_ru' => $this->body_short_ru,
            'body_short_es' => $this->body_short_es,
            'body_en' => $this->body_en,
            'body_ru' => $this->body_ru,
            'body_es' => $this->body_es,
            'sticky' => (bool) $this->sticky,
            'status' => (bool) $this->status,
            'image' => ImageResource::make($this->getFirstMedia(News::NEWS_PHOTO_COLLECTION_NAME)),
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
