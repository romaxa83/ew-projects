<?php

namespace WezomCms\Core\Traits;

use Artesaos\SEOTools\Traits\SEOTools;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Traits\Model\ImageAttachable;

trait OGImageTrait
{
    use SEOTools;

    /**
     * @param  ImageAttachable  $obj
     * @param  string  $field
     * @return  bool|void
     * @throws  \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    protected function setOGImage($obj, string $field = 'image')
    {
        $settings = config(array_get($obj->imageSettings(), $field));
        if (null === $settings) {
            return false;
        }

        $ogSize = array_get($settings, 'og_size');

        if ($obj->imageExists($ogSize)) {
            $imageUrl = ImageService::withoutWebp(function () use ($obj, $ogSize) {
                return $obj->getImageUrl($ogSize);
            });

            $this->seo()
                ->opengraph()
                ->addImage($imageUrl, $obj->getImageSize($ogSize));

            $this->seo()
                ->twitter()
                ->addImage($imageUrl);
        }
    }
}
