<?php

namespace App\Services\Media\Image;

use App\Services\Telegram\TelegramDev;
use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Image;
use Storage;

class ImageHandler
{
    protected const NO_IMAGE_DIR = 'no-image';

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * Image configurations.
     *
     * @var array
     */
    private $config;

    /**
     * ImageHandler constructor.
     * @param  Image  $image
     * @param  Filesystem  $storage
     */
    public function __construct(Image $image, Filesystem $storage)
    {
        $this->image = $image;
        $this->storage = $storage;

        $this->config = config('image', []);
    }

    /**
     * @param  Image  $image
     * @param  Filesystem  $storage
     * @return static
     */
    public static function make(Image $image, Filesystem $storage)
    {
        return new static($image, $storage);
    }

    /**
     * @param  int|null  $width
     * @param  int|null  $height
     * @param  string|null  $placeholder
     * @param  Filesystem|null  $storage
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     * @throws \Throwable
     */
    public static function noImage(
        $width = null,
        $height = null,
        ?string $placeholder = null,
        Filesystem $storage = null
    ) {
        $directory = config('cms.core.image.placeholders.directory');

        $placeholder = $placeholder !== null ? $placeholder : config('cms.core.image.placeholders.default');
        $sourcePath = "{$directory}/{$placeholder}";

        // If no sizes - return default no image.
        if (!$width && !$height) {
            return $sourcePath;
        }
        // Generate placeholder.
        if ($width && $height) {
            $fileName = $width . 'x' . $height;
        } elseif ($width) {
            $fileName = $width . 'x' . $width;
        } elseif ($height) {
            $fileName = $height . 'x' . $height;
        }

        $fileName .= '-' . $placeholder;

        if (null === $storage) {
            $storage = Storage::disk(config('cms.core.image.storage'));
        }

        $path = self::NO_IMAGE_DIR . '/' . $fileName;

        if ($storage->exists($path)) {
            return $path;
        }

        // Need generate
        $storage->createDir(self::NO_IMAGE_DIR);

        ImageHandler::make(\Image::make(public_path($sourcePath)), $storage)
            ->modify([
                'width' => $width,
                'height' => $height,
                'mode' => 'fit',
            ])
            ->save($path);

        return $path;
    }

    /**
     * @param  array|null  $settings
     * @return ImageHandler
     * @throws \Throwable
     */
    public function modify(array $settings = null): ImageHandler
    {
        $width = \Arr::get($settings, 'width');
        $height = \Arr::get($settings, 'height');

        switch (\Arr::get($settings, 'mode')) {
            case 'resizeCanvas':
                if ($width && $height) {
                    $this->image->resize($width, $height, function (Constraint $constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->resizeCanvas($width, $height, 'center', false, 'rgba(255,255,255,0)');
                }
                break;
            case 'fit':
                $this->image->fit($width, $height, function (Constraint $constraint) {
                    $constraint->upsize();
                });
                break;
            case 'resize':
                if ($width && $height) {
                    $this->image->resize($width, $height, function (Constraint $constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                } else {
                    if ($width && $this->image->width() > $width) {
                        $this->image->widen($width, function (Constraint $constraint) {
                            $constraint->upsize();
                        });
                    }

                    if ($height && $this->image->height() > $height) {
                        $this->image->heighten($height, function (Constraint $constraint) {
                            $constraint->upsize();
                        });
                    }
                }
                break;
        }

        // Watermark
        if (\Arr::get($settings, 'watermark')) {
            $this->watermark();
        }


        return $this;
    }

    public function orientate()
    {
        $this->image->orientate();

        return $this;
    }

    public function forcedOriental()
    {
        if(null !== $this->image->exif('Orientation')){
            $value = $this->image->exif('Orientation');

            switch ($value) {
                case 6:
                    $this->image->rotate(-90);
                    TelegramDev::info('rotate');
                    break;
            }
        }

        return $this;
    }

    /**
     * @param  string  $path
     * @return bool
     */
    public function save(string $path): bool
    {

        $result = $this->storage->put(
            $path,
            $this->image->encode(pathinfo($path, PATHINFO_EXTENSION), \Arr::get($this->config, 'quality', 100))
        );

        try {
            $webP = $this->image->encode('webp', 100);

            $this->storage->put("{$path}.webp", $webP);
        } catch (NotSupportedException $e) {
            if (app()->isLocal()) {
                report($e);
            }
        }

        return $result;
    }

    /**
     * Set image watermark.
     *
     * @throws \Throwable
     */
    public function watermark(): ImageHandler
    {
        $config = \Arr::get($this->config, 'watermark', []);
        try {
            $watermark = \Image::make(public_path($config['path']));

            if (\Arr::get($config, 'cover')) {
                $width = $this->image->width() >= $this->image->height() ? $this->image->width() : null;
                $height = $this->image->width() <= $this->image->height() ? $this->image->height() : null;

                $watermark->resize($width, $height, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });

                $watermark->opacity(\Arr::get($config, 'opacity', 100));

                $this->image->insert($watermark);
            } else {
                $size = \Arr::get($config, 'size', 40);
                $watermark->resize($this->image->width() / 100 * $size, null, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $watermark->opacity(\Arr::get($config, 'opacity', 100));

                $this->image->insert(
                    $watermark,
                    \Arr::get($config, 'position', 'bottom-left'),
                    \Arr::get($config, 'offset.x', 10),
                    \Arr::get($config, 'offset.y', 10)
                );
            }
        } catch (\Throwable $e) {
            report($e);

            if (config('app.debug')) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Destruct image instance.
     */
    public function __destruct()
    {
        $this->image = null;
    }
}
