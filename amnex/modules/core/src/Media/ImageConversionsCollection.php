<?php

declare(strict_types=1);

namespace Wezom\Core\Media;

use BackedEnum;
use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\ColorFormat;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\FlipDirection;
use Spatie\Image\Enums\Orientation;
use Spatie\Image\Enums\Unit;
use Spatie\ImageOptimizer\OptimizerChain;
use UnitEnum;
use Wezom\Core\Enums\Images\ImageSizeEnum;

/**
 * @method ImageConversionsCollection getSize()
 * @method ImageConversionsCollection gamma(float $gamma)
 * @method ImageConversionsCollection contrast(float $level)
 * @method ImageConversionsCollection blur(int $blur)
 * @method ImageConversionsCollection colorize(int $red, int $green, int $blue)
 * @method ImageConversionsCollection greyscale()
 * @method ImageConversionsCollection sepia()
 * @method ImageConversionsCollection sharpen(float $amount)
 * @method ImageConversionsCollection fit(Fit $fit, ?int $desiredWidth = null, ?int $desiredHeight = null)
 * @method ImageConversionsCollection pickColor(int $x, int $y, ColorFormat $colorFormat)
 * @method ImageConversionsCollection resizeCanvas(?int $width = null, ?int $height = null, ?AlignPosition $position = null, bool $relative = false, string $backgroundColor = '#000000')
 * @method ImageConversionsCollection manualCrop(int $width, int $height, int $x = 0, int $y = 0)
 * @method ImageConversionsCollection crop(int $width, int $height, CropPosition $position = CropPosition::Center)
 * @method ImageConversionsCollection focalCrop(int $width, int $height, ?int $cropCenterX = null, ?int $cropCenterY = null)
 * @method ImageConversionsCollection background(string $color)
 * @method ImageConversionsCollection overlay(ImageDriver $bottomImage, ImageDriver $topImage, int $x, int $y)
 * @method ImageConversionsCollection orientation(?Orientation $orientation = null)
 * @method ImageConversionsCollection flip(FlipDirection $flip)
 * @method ImageConversionsCollection pixelate(int $pixelate)
 * @method ImageConversionsCollection watermark(ImageDriver|string $watermarkImage, AlignPosition $position = AlignPosition::BottomRight, int $paddingX = 0, int $paddingY = 0, Unit $paddingUnit = Unit::Pixel, int $width = 0, Unit $widthUnit = Unit::Pixel, int $height = 0, Unit $heightUnit = Unit::Pixel, Fit $fit = Fit::Contain, int $alpha = 100)
 * @method ImageConversionsCollection insert(ImageDriver|string $otherImage, AlignPosition $position = AlignPosition::Center, int $x = 0, int $y = 0, int $alpha = 100)
 * @method ImageConversionsCollection defaultResize(int $width, int $height)
 * @method ImageConversionsCollection resize(int $width, int $height, array $constraints = [])
 * @method ImageConversionsCollection width(int $width, array $constraints = [])
 * @method ImageConversionsCollection height(int $height, array $constraints = [])
 * @method ImageConversionsCollection border(int $width, BorderType $type, string $color = '000000')
 * @method ImageConversionsCollection quality(int $quality)
 * @method ImageConversionsCollection format(string $format)
 * @method ImageConversionsCollection optimize(?OptimizerChain $optimizerChain = null)
 * @method ImageConversionsCollection nonOptimized()
 * @method ImageConversionsCollection getOperations()
 * @method ImageConversionsCollection getCollections()
 */
class ImageConversionsCollection
{
    /**
     * @var ImageConversion[]
     */
    private array $conversions = [];

    private array $collections = [];
    private array $last;

    private function __construct(array $collections)
    {
        $this->collections(...$collections);
    }

    public static function make(BackedEnum|UnitEnum|string ...$collections): ImageConversionsCollection
    {
        return new self($collections);
    }

    public function newCollection(BackedEnum|UnitEnum|string $collection): ImageConversionsCollection
    {
        return $this->collections($collection);
    }

    public function collections(BackedEnum|UnitEnum|string ...$collections): ImageConversionsCollection
    {
        $collectionNames = [];
        foreach ($collections as $collection) {
            $collectionNames[] = enum_to_string($collection);
        }

        $this->collections = $collectionNames;

        return $this;
    }

    public function addSize(ImageSizeEnum $size): ImageConversionsCollection
    {
        $conversion = ImageConversion::make($size)
            ->collections($this->collections);

        $webpConversion = ImageConversion::make($size->getWebpSize())
            ->format('webp')
            ->collections($this->collections);

        $webp2xConversion = ImageConversion::make($size->getWebp2XSize())
            ->format('webp')
            ->collections($this->collections);

        $this->conversions[] = $conversion;
        $this->conversions[] = $webpConversion;
        $this->conversions[] = $webp2xConversion;

        $this->last = [
            $conversion,
            $webpConversion,
            $webp2xConversion,
        ];

        return $this;
    }

    public function __call(string $name, array $arguments): ImageConversionsCollection
    {
        foreach ($this->last as $conversion) {
            $conversion->$name(...$arguments);
        }

        return $this;
    }

    /**
     * @return array<string, ImageConversion>
     */
    public function getConversions(): array
    {
        return $this->conversions;
    }
}
