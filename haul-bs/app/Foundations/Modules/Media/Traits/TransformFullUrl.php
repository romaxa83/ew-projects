<?php

namespace App\Foundations\Modules\Media\Traits;

use App\Foundations\Modules\Media\Models\File;
use App\Foundations\Modules\Media\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidConversion;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

trait TransformFullUrl
{
    public function fullUrl(Media|SpatieMedia|File $media, string|null $size = null): ?string
    {
        $s = config('filesystems.disks.s3');

        try {
            if($size){
                if($media->origin_id){
                    $fileName = current(explode('.', $media->file_name)) . '-' .$size . '.jpg';
                    return "https://{$s['bucket']}.{$s['driver']}.{$s['region']}.amazonaws.com/{$s['key']}/$media->origin_id/conversions/{$fileName}";
                } else {
                    return $media->getFullUrl($size);
                }
            }

            if($media->origin_id){
                return "https://{$s['bucket']}.{$s['driver']}.{$s['region']}.amazonaws.com/{$s['key']}/$media->origin_id/{$media->file_name}";
            } else {
                return $media->getFullUrl();
            }
        } catch (InvalidConversion $exception) {
            return null;
        }
    }
}

