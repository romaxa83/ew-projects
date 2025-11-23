<?php

namespace WezomCms\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use SEO;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Traits\Model\ImageAttachable;

trait OGImageTrait
{
    /**
     * @param  ImageAttachable|Model|mixed  $obj
     * @param  string  $field
     * @return  bool|void
     * @throws  \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    protected function setOGImage($obj, string $field = ImageService::IMAGE)
    {
        $settings = ImageService::extractSetting($obj, $field);
        if (null === $settings) {
            return false;
        }

        $ogSize = array_get($settings, 'og_size');

        if ($obj->imageExists($ogSize)) {
            $imageUrl = $obj->withoutWebp()->getImageUrl($ogSize);

            SEO::opengraph()->addImage($imageUrl, $obj->getImageSize($ogSize));

            SEO::twitter()->addImage($imageUrl);
        }
    }
}
