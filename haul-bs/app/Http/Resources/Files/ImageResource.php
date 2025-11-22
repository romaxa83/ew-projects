<?php

namespace App\Http\Resources\Files;

use App\Foundations\Modules\Media\Services\ImageResourceTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use App\Foundations\Modules\Media\Models\Media;

class ImageResource extends JsonResource
{
    /** @var ImageResourceTransformer */
    private $imageService;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->imageService = resolve(ImageResourceTransformer::class);
    }

    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="Image", type="object",
     *     @OA\Property(property="id", type="integer", description="File id"),
     *     @OA\Property(property="name", type="string", description="File name"),
     *     @OA\Property(property="file_name", type="string", description="Full file name"),
     *     @OA\Property(property="mime_type", type="string", description="Mime type"),
     *     @OA\Property(property="size", type="integer", description="File size"),
     *     @OA\Property(property="original", type="string", example="https://example.com/51b068b1927c6304d2131be2f0fca204-original_webp.webp",),
     *     @OA\Property(property="original_jpg", type="string", example="https://example.com/51b068b1927c6304d2131be2f0fca204-xs_jpg.jpg",),
     *     @OA\Property(property="xs", type="string", example="https://example.com/jihiuhmnsrf89fh9823742jshfj34-original_webp.webp", description="optional"),
     *     @OA\Property(property="xs_jpg", type="string", example="https://example.com/jihiuhmnsrf89fh9823742jshfj3-xs_jpg.jpg", description="optional"),
     *     @OA\Property(property="sm", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-original_webp.webp", description="optional"),
     *     @OA\Property(property="sm_jpg", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-xs_jpg.jpg", description="optional"),
     *     @OA\Property(property="md", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-original_webp.webp", description="optional"),
     *     @OA\Property(property="md_jpg", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-xs_jpg.jpg", description="optional"),
     *     @OA\Property(property="lg", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-original_webp.webp", description="optional"),
     *     @OA\Property(property="lg_jpg", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34-xs_jpg.jpg", description="optional"),
     * )
     *
     */
    public function toArray($request)
    {
        /** @var Media|SpatieMedia $image */
        $image =  $this;

        $webp = config('media-library.original_webp');

        return $this->imageService->toResource($image);
    }
}
