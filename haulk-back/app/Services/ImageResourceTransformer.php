<?php

namespace App\Services;

use App\Models\Files\File;
use App\Models\Files\ImageAbstract;
use Spatie\MediaLibrary\Models\Media;

class ImageResourceTransformer
{
    /**
     * @param Media|File $image
     * @return  array
     */
    public function toResource($image): array
    {
        $result = [
            'id' => $image->id,
            ImageAbstract::SIZE_ORIGINAL => $image->getFullUrl(),
        ];

        foreach ($image->getGeneratedConversions() as $size => $isResized) {
            if ($isResized) {
                $result[$size] = $image->getFullUrl($size);
            }
        }

        return $result;
    }
}
