<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use WezomCms\Core\Image\ImageService;

/**
 * Trait ImageAttachable
 * @method string placeholderFileName(string $field)
 */
trait ImageAttachable
{
    /**
     * Images configuration.
     * Example:
     * return [
     *     'image' => 'cms.core.administrator.images', // Set path to configuration array
     *     // or
     *     'image' => [ // array key - field name in DB.
     *         'directory' => 'administrators', // The name of the directory to store downloadable image.
     *         'multilingual' => true, // Image is multilingual
     *         'placeholder' => 'no-avatar.png',
     *         'og_size' => 'medium', // OpenGraph image size. Not required.
     *         'storage' => 'public', // Storage driver name. Not required.
     *         'original_storage' => 'public', // Storage driver name for store original files. Not required.
     *         'sizes' => [
     *             // sizes list
     *         ],
     *         'default' => 'medium',
     *     ],
     *     // Another fields
     * ];
     * @return array
     */
    abstract public function imageSettings(): array;

    /**
     * @param  SymfonyUploadedFile|SymfonyUploadedFile[]|array|mixed $source
     * @param  string  $field
     * @return bool
     */
    public function uploadImage($source, string $field = ImageService::IMAGE): bool
    {
        return app(ImageService::class)->uploadImage($this, $source, $field);
    }

    /**
     * @param  string  $field
     * @return bool
     */
    public function isImageMultilingual(string $field = ImageService::IMAGE): bool
    {
        $imageService = app(ImageService::class);

        return $imageService->isMultilingual($imageService->extractSetting($this, $field));
    }

    /**
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     * @throws \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    public function getImageUrl(
        string $size = null,
        string $field = ImageService::IMAGE,
        string $locale = null,
        $forCar = false,
        $forPromotion = false
    )
    {
        if(!$this->imageExists($size, $field, $locale)){
            if($forCar){
                return 'static/images/placeholders/no-marka.png';
            }
            if($forPromotion){
                return 'static/images/placeholders/niko.png';
            }
            return 'static/images/placeholders/no-image.png';
        }

        return app(ImageService::class)->getImageUrl($this, $size, $field, $locale);
    }

    /**
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return mixed
     * @throws \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    public function getExistImageUrl(string $size = null, string $field = ImageService::IMAGE, string $locale = null)
    {
        return app(ImageService::class)->getExistImageUrl($this, $size, $field, $locale);
    }

    /**
     * @param  string|null  $size
     * @param  string  $field
     * @param  string |null  $locale
     * @return bool
     * @throws \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    public function imageExists(string $size = null, string $field = ImageService::IMAGE, string $locale = null)
    {
        return app(ImageService::class)->imageExists($this, $size, $field, $locale);
    }

    /**
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function isSvg(string $field = ImageService::IMAGE, string $locale = null): bool
    {
        return app(ImageService::class)->isSvg($this, $field, $locale);
    }

    /**
     * @param  string|null  $field
     * @param  string|null  $locale
     * @return false|string
     * @throws \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    public function getSvgContent(string $field = ImageService::IMAGE, string $locale = null)
    {
        return app(ImageService::class)->getSvgContent($this, $field, $locale);
    }

    /**
     * @param  string|null  $size  - image size config key
     * @param  string  $field
     * @param  string|null  $locale
     * @return array - ['width' => 111, 'height' => 222]
     * @throws \WezomCms\Core\Image\Exceptions\IncorrectImageSizeException
     */
    public function getImageSize(string $size = null, string $field = ImageService::IMAGE, string $locale = null): array
    {
        return app(ImageService::class)->imageSize($this, $size, $field, $locale);
    }

    /**
     * @param  string  $field
     * @return array|null
     */
    public function getRecommendUploadImageSize(string $field = ImageService::IMAGE): ?array
    {
        return app(ImageService::class)->getRecommendUploadImageSize($this, $field);
    }

    /**
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function deleteImage(string $field = ImageService::IMAGE, string $locale = null): bool
    {
        return app(ImageService::class)->deleteImage($this, $field, null, $locale);
    }

    /**
     * @param  bool  $save
     * @return bool
     */
    public function deleteAllImages(bool $save = false): bool
    {
        return app(ImageService::class)->deleteAllImages($this, $save);
    }

    /**
     * Hook into the Eloquent model events.
     */
    public static function bootImageAttachable()
    {
        static::deleting(function (Model $model) {
            /** @var $model ImageAttachable */
            $model->deleteAllImages();
        });
    }
}
