<?php

namespace App\Foundations\Modules\Seo\Deliveries\Http\Resources;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Http\Resources\Files\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SeoResource", type="object", allOf={
 *     @OA\Schema(
 *         @OA\Property(property="h1", type="string", example="Some H1", nullable=true),
 *         @OA\Property(property="title", type="string", example="Some title", nullable=true),
 *         @OA\Property(property="desc", type="string", example="Some description", nullable=true),
 *         @OA\Property(property="keywords", type="string", example="Some keywords", nullable=true),
 *         @OA\Property(property="text", type="string", example="Some text", nullable=true),
 *         @OA\Property(property="image", type="object", description="image with different size", allOf={
 *             @OA\Schema(ref="#/components/schemas/Image")
 *         }),
 *     )}
 * )
 *
 * @OA\Schema(schema="SeoRequest", type="object", allOf={
 *      @OA\Schema(
 *          @OA\Property(property="h1", type="string", example="Some H1", nullable=true),
 *          @OA\Property(property="title", type="string", example="Some title", nullable=true),
 *          @OA\Property(property="desc", type="string", example="Some description", nullable=true),
 *          @OA\Property(property="keywords", type="string", example="Some keywords", nullable=true),
 *          @OA\Property(property="text", type="string", example="Some text", nullable=true),
 *          @OA\Property(property="image", type="string", format="binary", nullable=true , description="The file to upload",),
 *      )}
 *  )
 *
 * @mixin Seo
 */

class SeoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'h1' => $this->h1,
            'title' => $this->title,
            'keywords' => $this->keywords,
            'desc' => $this->desc,
            'text' => $this->text,
            $this->getImageField() => ImageResource::make($this->getFirstImage()),
        ];
    }
}
