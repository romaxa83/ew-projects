<?php

namespace App\Http\Resources\Files;

use App\Models\Files\File;
use App\Services\ImageResourceTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;

class ImageGeoResource extends JsonResource
{
    /**
     * @var ImageResourceTransformer
     */
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
     * @OA\Schema(schema="ImageGeo", type="object",
     *  @OA\Property(property="original", type="string", example="https://example.com/jihiuhmdjfh9823742jshfj34.jpg",),
     *  @OA\Property(property="xs", type="string", example="https://example.com/jihiuhmnsrf89fh9823742jshfj34.jpg", description="optional"),
     *  @OA\Property(property="sm", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34.jpg", description="optional"),
     *  @OA\Property(property="md", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34.jpg", description="optional"),
     *  @OA\Property(property="lg", type="string", example="https://example.com/234rtyyjlwklefh9823742jshfj34.jpg", description="optional"),
     *  @OA\Property(property="lat", type="number",),
     *  @OA\Property(property="lng", type="number",),
     *  @OA\Property(property="created_at", type="integer",),
     *  @OA\Property(property="created_timezone", type="string",),
     * )
     *
     */
    public function toArray($request)
    {
        /** @var Media|File $image */
        $image =  $this;

        return $this->imageService->toResource($image) + [
            'lat' => $image->getCustomProperty('lat') ? (double) $image->getCustomProperty('lat') : null,
            'lng' => $image->getCustomProperty('lng') ? (double) $image->getCustomProperty('lng') : null,
            'created_at' => $image->getCustomProperty('timestamp') ? (double) $image->getCustomProperty('timestamp') : null,
            'created_timezone' => $image->getCustomProperty('timezone') ?? null,
        ];
    }
}
