<?php

namespace App\Foundations\Modules\Media\Services;

use App\Foundations\Modules\Media\Images\ImageAbstract;
use App\Foundations\Modules\Media\Models\File;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class ImageResourceTransformer
{
    use TransformFullUrl;

    /**
     * @param Media|SpatieMedia $image
     * @return  array
     */
    public function toResource($image): array
    {
        $result = [
            'id' => $image->id,
            'name' => $image->name,
            'file_name' => $image->file_name,
            'mime_type' => $image->mime_type,
            'size' => $image->size,
            ImageAbstract::SIZE_ORIGINAL => $this->fullUrl($image->resource),
        ];

        foreach ($image->getGeneratedConversions() as $size => $isResized) {
            if ($isResized && $image->hasGeneratedConversion($size)) {
                /**
                 * в итоговом результате линки под оригинальными размерами (original, xs, md ....), будут файлы
                 * с разрешением webp, остальные в формате {размер}_{формат} (original_jpg, xs_jpg),
                 * т.к. у всех такой формат то для webp убираем суффикс
                 */
                $sizeName = str_replace(config('media-library.webp_conversion_suffix'), null, $size);
                $result[$sizeName] = $this->fullUrl($image->resource, $size);
            }
        }

        return $result;
    }

    public function toRequest(Media|SpatieMedia|File $image): array
    {
        $result = [
            'id' => $image->id,
            'name' => $image->name,
            'file_name' => $image->file_name,
            'mime_type' => $image->mime_type,
            'order_column' => $image->order_column,
            'size' => $image->size,
            ImageAbstract::SIZE_ORIGINAL => $this->fullUrl($image),
        ];

        foreach ($image->getGeneratedConversions() as $size => $isResized) {
            if ($isResized) {
                $sizeName = str_replace(config('media-library.webp_conversion_suffix'), null, $size);
                $result[$sizeName] = $this->fullUrl($image, $size);
            }
        }

        return $result;
    }
}
