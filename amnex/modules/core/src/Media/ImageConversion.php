<?php

declare(strict_types=1);

namespace Wezom\Core\Media;

use Spatie\Image\Drivers\ImageDriver;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\BorderType;
use Spatie\Image\Enums\ColorFormat;
use Spatie\Image\Enums\Constraint;
use Spatie\Image\Enums\CropPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\FlipDirection;
use Spatie\Image\Enums\Orientation;
use Spatie\Image\Enums\Unit;
use Spatie\ImageOptimizer\OptimizerChain;
use Wezom\Core\Enums\Images\ImageSizeEnum;

class ImageConversion
{
    private const SIZE_ARGUMENTS = [
        'width',
        'height',
        'desiredWidth',
        'desiredHeight',
        'x',
        'y',
        'cropCenterX',
        'cropCenterY',
        'paddingX',
        'paddingY',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $operations = [];

    private bool $sizeX2 = false;
    private array $collections;

    final private function __construct(private readonly ImageSizeEnum $size)
    {
        if ($size->is2xSize()) {
            $this->sizeX2 = true;
        }
    }

    public static function make(ImageSizeEnum $size): static
    {
        return new static($size);
    }

    public function getSize(): ImageSizeEnum
    {
        return $this->size;
    }

    public function gamma(float $gamma): static
    {
        return $this->addOperation(ConversionOperationEnum::GAMMA, compact('gamma'));
    }

    public function contrast(float $level): static
    {
        return $this->addOperation(ConversionOperationEnum::CONTRAST, compact('level'));
    }

    public function blur(int $blur): static
    {
        return $this->addOperation(ConversionOperationEnum::BLUR, compact('blur'));
    }

    public function colorize(int $red, int $green, int $blue): static
    {
        return $this->addOperation(ConversionOperationEnum::COLORIZE, compact('red', 'green', 'blue'));
    }

    public function greyscale(): static
    {
        return $this->addOperation(ConversionOperationEnum::GREYSCALE, []);
    }

    public function sepia(): static
    {
        return $this->addOperation(ConversionOperationEnum::SEPIA, []);
    }

    public function sharpen(float $amount): static
    {
        return $this->addOperation(ConversionOperationEnum::SHARPEN, compact('amount'));
    }

    public function fit(Fit $fit, ?int $desiredWidth = null, ?int $desiredHeight = null): static
    {
        return $this->addOperation(ConversionOperationEnum::FIT, compact('fit', 'desiredWidth', 'desiredHeight'));
    }

    public function pickColor(int $x, int $y, ColorFormat $colorFormat): static
    {
        return $this->addOperation(ConversionOperationEnum::PICK_COLOR, compact('x', 'y', 'colorFormat'));
    }

    public function resizeCanvas(
        ?int $width = null,
        ?int $height = null,
        ?AlignPosition $position = null,
        bool $relative = false,
        string $backgroundColor = '#000000'
    ): static {
        return $this->addOperation(
            ConversionOperationEnum::RESIZE_CANVAS,
            compact('width', 'height', 'position', 'relative', 'backgroundColor')
        );
    }

    public function manualCrop(int $width, int $height, int $x = 0, int $y = 0): static
    {
        return $this->addOperation(
            ConversionOperationEnum::MANUAL_CROP,
            compact('width', 'height', 'x', 'y')
        );
    }

    public function crop(int $width, int $height, CropPosition $position = CropPosition::Center): static
    {
        return $this->addOperation(
            ConversionOperationEnum::CROP,
            compact('width', 'height', 'position')
        );
    }

    public function focalCrop(int $width, int $height, ?int $cropCenterX = null, ?int $cropCenterY = null): static
    {
        return $this->addOperation(
            ConversionOperationEnum::FOCAL_CROP,
            compact('width', 'height', 'cropCenterX', 'cropCenterY')
        );
    }

    public function background(string $color): static
    {
        return $this->addOperation(ConversionOperationEnum::BACKGROUND, compact('color'));
    }

    public function overlay(ImageDriver $bottomImage, ImageDriver $topImage, int $x, int $y): static
    {
        return $this->addOperation(ConversionOperationEnum::OVERLAY, compact('bottomImage', 'topImage', 'x', 'y'));
    }

    public function orientation(?Orientation $orientation = null): static
    {
        return $this->addOperation(ConversionOperationEnum::ORIENTATION, compact('orientation'));
    }

    public function flip(FlipDirection $flip): static
    {
        return $this->addOperation(ConversionOperationEnum::FLIP, compact('flip'));
    }

    public function pixelate(int $pixelate): static
    {
        return $this->addOperation(ConversionOperationEnum::PIXELATE, compact('pixelate'));
    }

    public function watermark(
        ImageDriver|string $watermarkImage,
        AlignPosition $position = AlignPosition::BottomRight,
        int $paddingX = 0,
        int $paddingY = 0,
        Unit $paddingUnit = Unit::Pixel,
        int $width = 0,
        Unit $widthUnit = Unit::Pixel,
        int $height = 0,
        Unit $heightUnit = Unit::Pixel,
        Fit $fit = Fit::Contain,
        int $alpha = 100
    ): static {
        return $this->addOperation(
            ConversionOperationEnum::WATERMARK,
            compact(
                'watermarkImage',
                'position',
                'paddingX',
                'paddingY',
                'paddingUnit',
                'width',
                'widthUnit',
                'height',
                'heightUnit',
                'fit',
                'alpha'
            )
        );
    }

    public function insert(
        ImageDriver|string $otherImage,
        AlignPosition $position = AlignPosition::Center,
        int $x = 0,
        int $y = 0,
        int $alpha = 100
    ): static {
        return $this->addOperation(
            ConversionOperationEnum::INSERT,
            compact('otherImage', 'position', 'x', 'y', 'alpha')
        );
    }

    public function defaultResize(int $width, int $height): static
    {
        $constraints = [Constraint::PreserveAspectRatio, Constraint::DoNotUpsize];

        return $this->resize($width, $height, $constraints)->quality(100);
    }

    /** @param  array<Constraint>  $constraints */
    public function resize(int $width, int $height, array $constraints = []): static
    {
        return $this->addOperation(ConversionOperationEnum::RESIZE, compact('width', 'height', 'constraints'));
    }

    /** @param  array<Constraint>  $constraints */
    public function width(int $width, array $constraints = []): static
    {
        return $this->addOperation(ConversionOperationEnum::WIDTH, compact('width', 'constraints'));
    }

    /** @param  array<Constraint>  $constraints */
    public function height(int $height, array $constraints = []): static
    {
        return $this->addOperation(ConversionOperationEnum::HEIGHT, compact('height', 'constraints'));
    }

    public function border(int $width, BorderType $type, string $color = '000000'): static
    {
        return $this->addOperation(ConversionOperationEnum::BORDER, compact('width', 'type', 'color'));
    }

    public function quality(int $quality): static
    {
        return $this->addOperation(ConversionOperationEnum::QUALITY, compact('quality'));
    }

    public function format(string $format): static
    {
        return $this->addOperation(ConversionOperationEnum::FORMAT, compact('format'));
    }

    public function optimize(?OptimizerChain $optimizerChain = null): static
    {
        return $this->addOperation(ConversionOperationEnum::OPTIMIZE, compact('optimizerChain'));
    }

    public function nonOptimized(): static
    {
        return $this->addOperation(ConversionOperationEnum::NON_OPTIMIZED, []);
    }

    private function addOperation(ConversionOperationEnum $operation, array $attributes): static
    {
        $this->operations[$operation->value] = $this->transformSizeTo2x($attributes);

        return $this;
    }

    private function transformSizeTo2x(array $arguments): array
    {
        if (!$this->sizeX2) {
            return $arguments;
        }

        foreach ($arguments as $key => &$value) {
            if ($this->isSizeArgument($key)) {
                $value *= 2;
            }
        }

        return $arguments;
    }

    private function isSizeArgument(string $key): bool
    {
        return in_array($key, self::SIZE_ARGUMENTS);
    }

    public function collections(array $collections): static
    {
        $this->collections = $collections;

        return $this;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getCollections(): array
    {
        return $this->collections;
    }
}
