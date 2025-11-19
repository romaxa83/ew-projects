<?php

declare(strict_types=1);

namespace Wezom\Core\Rules;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Image;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Wezom\Core\Contracts\Extensions\Extension;
use Wezom\Core\Enums\Images\ImageExtension;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ImageRulesAttribute extends Rule
{
    /** @var ImageExtension[] */
    public const array DEFAULT_EXTENSIONS = [ImageExtension::JPG, ImageExtension::JPEG, ImageExtension::PNG];

    public const int DEFAULT_SIZE = 2048;

    public function __construct(
        /** @var ImageExtension[] */
        protected array $extensions = self::DEFAULT_EXTENSIONS,
        protected int $size = self::DEFAULT_SIZE,
    ) {
        parent::__construct(...$this->prepareRules());
    }

    protected function prepareRules(): array
    {
        return [
            $this->getImageAttribute(),
            $this->getMaxSizeAttribute(),
            $this->getMimesAttribute(),
        ];
    }

    public function getImageAttribute(): Image
    {
        return new Image();
    }

    public function getMaxSizeAttribute(): Max
    {
        return new Max($this->getMaxSize());
    }

    public function getMaxSize(): int
    {
        return $this->size;
    }

    public function getMimesAttribute(): Mimes
    {
        return new Mimes(
            $this->getExtensionsAsStrings(
                $this->getExtensions()
            )
        );
    }

    /** @return ImageExtension[] */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param ImageExtension[] $extensions
     * @return string[]
     */
    private function getExtensionsAsStrings(array $extensions): array
    {
        return array_map(
            static fn (Extension $extension): string => $extension->get(),
            $extensions
        );
    }
}
