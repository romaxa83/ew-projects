<?php

namespace App\Models\Storage;

use Exception;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class AwsPathGenerator extends BasePathGenerator
{
    /**
     * @param Media $media
     * @return string
     * @throws Exception
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param Media $media
     * @return string
     * @throws Exception
     */
    protected function getBasePath(Media $media): string
    {
        if ($basePath = config('medialibrary.directory')) {
            return $basePath . DIRECTORY_SEPARATOR . parent::getBasePath($media);
        }

        throw new Exception('Base aws root path must be set.');
    }

    /**
     * @param Media $media
     * @return string
     * @throws Exception
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . DIRECTORY_SEPARATOR . 'conversions' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param Media $media
     * @return string
     * @throws Exception
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . DIRECTORY_SEPARATOR . 'responsive-images' . DIRECTORY_SEPARATOR;
    }
}
