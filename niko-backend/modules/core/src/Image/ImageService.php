<?php

namespace WezomCms\Core\Image;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Image;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Exception\NotReadableException;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use WezomCms\Core\ExtendPackage\Translatable;
use WezomCms\Core\Image\Exceptions\IncorrectImageSizeException;
use WezomCms\Core\Traits\Model\ImageAttachable;

/**
 * Class ImageService
 * @package WezomCms\Core\Image
 */
class ImageService
{
    public const IMAGE = 'image';
    protected const DONT_MODIFY_EXT = ['svg', 'gif'];

    /**
     * @var array
     */
    protected $mimeExtensionsMap = [
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpeg',
        'image/pjpeg' => 'jpeg',
        'image/png' => 'png',
        'image/svg+xml' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @var array
     */
    private $locales;

    /**
     * @var string
     */
    private $defaultDisk;

    /**
     * @var string
     */
    private $defaultOriginalDisk;

    /**
     * Enable/Disable webp definition.
     *
     * @var bool
     */
    public static $webpDefinition = true;

    /**
     * ImageService constructor.
     */
    public function __construct()
    {
        $this->locales = array_keys(app('locales'));

        $this->defaultDisk = config('cms.core.image.storage');

        $this->defaultOriginalDisk = config('cms.core.image.original_storage');
    }

    /**
     * @param  Model|SoftDeletes|ImageAttachable  $model
     * @param  bool  $save
     * @return bool
     */
    public function deleteAllImages(Model $model, bool $save = true): bool
    {
        if (method_exists($model, 'trashed') && $model->isForceDeleting() === false) {
            return true;
        }

        foreach (array_keys($model->imageSettings()) as $field) {
            $this->deleteImage($model, $field, $this->extractSetting($model, $field), null, $save);
        }


        return true;
    }

    /**
     * @param  Model|ImageAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     */
    public function isSvg(Model $model, string $field = self::IMAGE, string $locale = null): bool
    {
        $storedFileName = $this->getStoredFileName($model, $this->extractSetting($model, $field), $field, $locale);

        return mb_strtolower(pathinfo($storedFileName, PATHINFO_EXTENSION)) === 'svg';
    }

    /**
     * @param  Model|ImageAttachable  $model
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     * @throws IncorrectImageSizeException
     * @throws FileNotFoundException
     */
    public function getSvgContent(Model $model, string $field = self::IMAGE, string $locale = null): ?string
    {
        $setting = $this->extractSetting($model, $field);

        $storedFileName = $this->getStoredFileName($model, $setting, $field, $locale);

        if (!$storedFileName) {
            return null;
        }

        if (!$this->imageExists($model, null, $field, $locale)) {
            return null;
        }

        $path = $this->buildPath([
            array_get($setting, 'directory'),
            $this->getCorrectSize($model, null, $setting),
            $storedFileName
        ]);

        return $this->getStorage($setting)->get($path);
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  UploadedFile|UploadedFile[]|array|mixed  $source
     * @param  string  $field
     * @return bool
     * @throws \Throwable
     */
    public function uploadImage(Model $model, $source, string $field = self::IMAGE): bool
    {
        $setting = $this->extractSetting($model, $field);

        $sizes = array_get($setting, 'sizes', []);

        $directory = array_get($setting, 'directory');
        $storage = $this->getStorage($setting);
        $originalStorage = $this->getOriginalStorage($setting);
        if ($this->isMultilingual($setting)) {
            foreach ($this->locales as $locale) {
                if (is_array($source) && $item = array_get($source, $locale)) {
                    if ($item instanceof SymfonyUploadedFile && $item->getError() !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    if ($fileName = $this->storeFile($item, $directory, $storage, $originalStorage, $sizes)) {
                        $obj = $model->translateOrNew($locale);
                        $obj->withoutEvents(function () use ($obj, $field, $fileName) {
                            $obj->setAttribute($field, $fileName)
                                ->save();
                        });
                    }
                }
            }
        } else {
            if (!$source || ($source instanceof SymfonyUploadedFile && $source->getError() !== UPLOAD_ERR_OK)) {
                return false;
            }

            if ($fileName = $this->storeFile($source, $directory, $storage, $originalStorage, $sizes)) {
                $model->withoutEvents(function () use ($model, $field, $fileName) {
                    $model->setAttribute($field, $fileName)
                        ->save();
                });
            }
        }

        return true;
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  string  $field
     * @param  array  $setting
     * @param  string|null  $locale
     * @param  bool  $save
     * @return bool
     */
    public function deleteImage(
        Model $model,
        string $field = self::IMAGE,
        array $setting = null,
        string $locale = null,
        bool $save = true
    ) {
        if (null === $setting) {
            $setting = $this->extractSetting($model, $field);
        }

        $directory = array_get($setting, 'directory');

        $storage = $this->getStorage($setting);
        $originalStorage = $this->getOriginalStorage($setting);
        $sizes = array_get($setting, 'sizes', []);
        if ($this->isMultilingual($setting)) {
            if (null !== $locale) {
                // delete concrete locale
                $fileName = $this->getStoredFileName($model, $setting, $field, $locale);

                if ($fileName) {
                    $this->deleteOnlyFiles($sizes, $directory, $storage, $originalStorage, $fileName);
                }

                $model->translateOrNew($locale)->setAttribute($field, null);
            } else {
                // delete all locales
                foreach ($this->locales as $localLocale) {
                    $fileName = $this->getStoredFileName($model, $setting, $field, $localLocale);

                    if ($fileName) {
                        $this->deleteOnlyFiles($sizes, $directory, $storage, $originalStorage, $fileName);
                    }

                    $model->translateOrNew($localLocale)->setAttribute($field, null);
                }
            }
        } else {
            // Delete not translatable image
            $fileName = $model->getAttribute($field);

            if ($fileName) {
                $this->deleteOnlyFiles($sizes, $directory, $storage, $originalStorage, $fileName);
            }

            $model->setAttribute($field, null);
        }

        if ($save) {
            $model->save();
        }

        return true;
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return bool
     * @throws IncorrectImageSizeException
     */
    public function imageExists(Model $model, string $size = null, string $field = self::IMAGE, string $locale = null)
    {
        $setting = $this->extractSetting($model, $field);

        $storedFileName = $this->getStoredFileName($model, $setting, $field, $locale);

        if (!$storedFileName) {
            return false;
        }

        $path = $this->buildPath([
            array_get($setting, 'directory'),
            $this->getCorrectSize($model, $size, $setting),
            $storedFileName
        ]);

        return $this->getStorage($setting)->exists($path);
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return array
     * @throws IncorrectImageSizeException
     * @throws FileNotFoundException
     */
    public function imageSize(Model $model, string $size = null, string $field = self::IMAGE, string $locale = null)
    {
        $setting = $this->extractSetting($model, $field);

        $storedFileName = $this->getStoredFileName($model, $setting, $field, $locale);

        $path = $this->buildPath([
            array_get($setting, 'directory'),
            $this->getCorrectSize($model, $size, $setting),
            $storedFileName
        ]);

        $storage = $this->getStorage($setting);

        if ($storedFileName && $storage->exists($path)) {
            try {
                $image = Image::make($storage->readStream($path));

                return [
                    'width' => $image->width() ?: 0,
                    'height' => $image->height() ?: 0,
                ];
            } catch (ImageException $e) {
                report($e);
            }
        }

        return [
            'width' => 0,
            'height' => 0,
        ];
    }

    /**
     * @param  Model|ImageAttachable  $model
     * @param  string  $field
     * @return array|null
     */
    public function getRecommendUploadImageSize($model, string $field = self::IMAGE): ?array
    {
        $sizes = array_get($this->extractSetting($model, $field), 'sizes', []);

        if (!$sizes) {
            return null;
        }

        $width = array_column($sizes, 'width');
        $height = array_column($sizes, 'height');

        if (!$width || !$height) {
            return null;
        }

        return [
            'width' => max($width),
            'height' => max($height),
        ];
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return string
     * @throws IncorrectImageSizeException
     */
    public function getImageUrl(Model $model, string $size = null, string $field = self::IMAGE, string $locale = null)
    {
        $setting = $this->extractSetting($model, $field);

        $size = $this->getCorrectSize($model, $size, $setting);

        $storage = $this->getStorage($setting);

        if ($this->imageExists($model, $size, $field, $locale)) {
            $path = $this->buildPath([
                array_get($setting, 'directory'),
                $size,
                $this->getStoredFileName($model, $setting, $field, $locale),
            ]);
        } else {
            $sizeSetting = array_get($setting, 'sizes.' . $size, []);

            // Make placeholder
            $path = ImageHandler::noImage(
                array_get($sizeSetting, 'width'),
                array_get($sizeSetting, 'height'),
                method_exists($model, 'placeholderFileName')
                    ? $model->placeholderFileName($field)
                    : array_get($setting, 'placeholder'),
                $storage
            );
        }

        if (static::webPSupport() && $storage->exists($path . '.webp')) {
            return $storage->url($path . '.webp');
        }

        return $storage->url($path);
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  string|null  $size
     * @param  string  $field
     * @param  string|null  $locale
     * @return string|null
     * @throws IncorrectImageSizeException
     */
    public function getExistImageUrl(
        Model $model,
        string $size = null,
        string $field = self::IMAGE,
        string $locale = null
    ) {
        return $this->imageExists($model, $size, $field, $locale)
            ? $this->getImageUrl($model, $size, $field, $locale)
            : null;
    }

    /**
     * @param  Model|ImageAttachable  $model
     * @param $field
     * @return bool
     */
    public function deleteLostImages(Model $model, $field): bool
    {
        $settings = $this->extractSetting($model, $field);

        $dbFiles = $model->select($field)->pluck($field)->toArray();

        $directory = array_get($settings, 'directory');

        $storage = $this->getStorage($settings);

        foreach (array_keys(array_get($settings, 'sizes', [])) as $size) {
            foreach ($storage->files("{$directory}/{$size}") as $file) {
                if (!in_array(preg_replace('/\.webp$/', '', pathinfo($file, PATHINFO_BASENAME)), $dbFiles)) {
                    $storage->delete($file);
                }
            }
        }

        foreach ($storage->files($directory) as $file) {
            if (!in_array(preg_replace('/\.webp$/', '', pathinfo($file, PATHINFO_BASENAME)), $dbFiles)) {
                $storage->delete($file);
            }
        }

        return true;
    }

    /**
     * @param  Model  $model
     * @param  string  $field
     * @param  OutputStyle|null  $output
     * @return bool
     * @throws \Throwable
     */
    public function reCropImages(Model $model, string $field, ?OutputStyle $output = null): bool
    {
        $settings = $this->extractSetting($model, $field);

        $directory = array_get($settings, 'directory');
        $sizes = array_get($settings, 'sizes', []);

        $storage = $this->getStorage($settings);
        $originalStorage = $this->getOriginalStorage($settings);

        $originalFiles = $originalStorage->files("{$directory}/original");

        $countOriginalFiles = count($originalFiles);
        if ($countOriginalFiles === 0) {
            return false;
        }

        if ($output) {
            $output->progressStart($countOriginalFiles * count($sizes));
        }

        foreach ($originalFiles as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);

            $originalFileContent = $originalStorage->get($file);

            foreach ($sizes as $size => $sizeSettings) {
                $targetFile = "{$directory}/{$size}/{$fileName}";

                $storage->delete($targetFile, $targetFile . '.webp');

                ImageHandler::make(Image::make($originalFileContent), $storage)
                    ->modify($sizeSettings)
                    ->save($targetFile);

                if ($output) {
                    $output->progressAdvance();
                }
            }
        }

        if ($output) {
            $output->progressFinish();
        }

        return true;
    }

    /**
     * @return bool
     */
    public static function webPSupport(): bool
    {
        if (!static::$webpDefinition) {
            return false;
        }

        return in_array('image/webp', \Request::getAcceptableContentTypes());
    }

    /**
     * @param  Closure  $callback
     * @return mixed
     */
    public static function withoutWebp(Closure $callback)
    {
        try {
            static::$webpDefinition = false;

            return $callback();
        } finally {
            static::$webpDefinition = true;
        }
    }

    /**
     * @param  Model|ImageAttachable  $model
     * @param  string  $field
     * @return array
     */
    public function extractSetting(Model $model, string $field = self::IMAGE): array
    {
        $setting = array_get($model->imageSettings(), $field);

        if (is_array($setting)) {
            return $setting;
        } else {
            return config()->get($setting, []);
        }
    }

    /**
     * @param  array  $setting
     * @return bool
     */
    public function isMultilingual(array $setting): bool
    {
        return array_get($setting, 'multilingual', false);
    }

    /**
     * @param  Model|Translatable|ImageAttachable  $model
     * @param  array  $setting
     * @param  string  $field
     * @param  string|null  $locale
     * @return mixed
     */
    private function getStoredFileName(Model $model, array $setting, string $field = self::IMAGE, string $locale = null)
    {
        return $this->isMultilingual($setting)
            ? $model->translateOrNew($locale)->getAttribute($field)
            : $model->getAttribute($field);
    }

    /**
     * @param  array  $sizes
     * @param  string|null  $directory
     * @param  FilesystemAdapter  $storage
     * @param  FilesystemAdapter  $originalStorage
     * @param  string  $fileName
     */
    private function deleteOnlyFiles(
        array $sizes,
        ?string $directory,
        FilesystemAdapter $storage,
        FilesystemAdapter $originalStorage,
        string $fileName
    ) {
        // Delete all size images.
        if (count($sizes)) {
            foreach ($sizes as $size => $settings) {
                $sizeDir = rtrim($directory, '/') . '/' . $size;

                // Delete concrete file size.
                $this->unlinkExistFile($storage, "{$sizeDir}/{$fileName}");
            }

            $this->unlinkExistFile($originalStorage, "{$directory}/original/{$fileName}");
        }

        // Delete global image without sizes.
        $this->unlinkExistFile($storage, "{$directory}/{$fileName}");
    }

    /**
     * @param  SymfonyUploadedFile  $file
     * @param  string|null  $directory
     * @param  FilesystemAdapter  $storage
     * @param  FilesystemAdapter  $originalStorage
     * @param $sizes
     * @return string|null
     * @throws \Throwable
     */
    private function storeFile(
        $file,
        ?string $directory,
        FilesystemAdapter $storage,
        FilesystemAdapter $originalStorage,
        $sizes
    ): ?string {
        if ($file instanceof SymfonyUploadedFile) {
            $extension = $file->getClientOriginalExtension();
        } else {
            try {
                $image = Image::make($file);
                $extension = $image->extension;
                if (!$extension) {
                    $extension = $this->getExtension($image->mime());
                }

                unset($image);
            } catch (NotReadableException $e) {
                report($e);

                return null;
            }
        }

        if (!$extension) {
            return null;
        }



        $dontModify = in_array($extension, self::DONT_MODIFY_EXT);

        $fileName = Str::random(40) . '.' . $extension;

        if ($sizes) {
            // Image storage for each size.
            foreach ($sizes as $size => $settings) {
                $destinationDir = "{$directory}/{$size}";
                $storage->makeDirectory($destinationDir);

                if ($dontModify) {
                    $storage->putFileAs($destinationDir, $file, $fileName);
                } else {
                    ImageHandler::make(Image::make($file), $storage)
                        ->modify($settings)
                        ->save("{$destinationDir}/{$fileName}");

                    $originalStorage->put("{$directory}/original/{$fileName}", Image::make($file)->encode());
                }
            }
        } else {
            // Save original file.
            $storage->makeDirectory($directory);
            if ($dontModify) {
                $storage->putFileAs($directory, $file, $fileName);
            } else {
                $storage->put("{$directory}/{$fileName}", Image::make($file)->encode());
            }
        }

        return $fileName;
    }

    /**
     * @param  Model  $model
     * @param  string|null  $size
     * @param  array  $setting
     * @return string|null
     * @throws IncorrectImageSizeException
     */
    private function getCorrectSize(Model $model, ?string $size, array $setting = []): ?string
    {
        if ($size === null) {
            // Try select default size.
            $size = array_get($setting, 'default');
        }

        $allSizes = array_get($setting, 'sizes', []);

        // If size not supported - reset size to null.
        if (empty($allSizes)) {
            return null;
        } elseif (!array_key_exists($size, $allSizes)) {
            throw new IncorrectImageSizeException(
                sprintf('Model [%s] doesn\'t contain image size "%s"', get_class($model), $size)
            );
        }

        return $size;
    }

    /**
     * @param  FilesystemAdapter  $storage
     * @param  string  $path
     */
    protected function unlinkExistFile(FilesystemAdapter $storage, string $path)
    {
        if ($storage->exists($path)) {
            $storage->delete($path);
        }

        if ($storage->exists($path . '.webp')) {
            $storage->delete($path . '.webp');
        }
    }

    /**
     * Get extension using mime type from the supported list
     *
     * @param  string  $mime
     * @return string|null
     */
    protected function getExtension($mime): ?string
    {
        return array_get($this->mimeExtensionsMap, $mime);
    }

    /**
     * @param  array  $setting
     * @return FilesystemAdapter
     */
    protected function getStorage(array $setting): FilesystemAdapter
    {
        return Storage::disk(array_get($setting, 'storage', $this->defaultDisk));
    }

    /**
     * @param  array  $setting
     * @return FilesystemAdapter
     */
    protected function getOriginalStorage(array $setting): FilesystemAdapter
    {
        return Storage::disk(array_get($setting, 'original_storage', $this->defaultOriginalDisk));
    }

    /**
     * @param  array  $parts
     * @return string
     */
    protected function buildPath(array $parts): string
    {
        return implode('/', array_filter($parts));
    }
}
