<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Wezom\Core\Contracts\ImageConversionInterface;
use Wezom\Core\Enums\Images\ImageSizeEnum;
use Wezom\Core\Media\ImageConversionsCollection;

class TestingImageConversions implements ImageConversionInterface
{
    public function register(): ImageConversionsCollection
    {
        return ImageConversionsCollection::make()
            ->newCollection('collection1')
            ->addSize(ImageSizeEnum::BIG)
            ->defaultResize(1478, 731)
            ->nonOptimized()
            ->addSize(ImageSizeEnum::MEDIUM)
            ->defaultResize(948, 639)
            ->nonOptimized()
            ->addSize(ImageSizeEnum::SMALL)
            ->defaultResize(573, 426)
            ->nonOptimized()
            ->newCollection('collection2')
            ->addSize(ImageSizeEnum::BIG)
            ->defaultResize(500, 200)
            ->nonOptimized()
            ->addSize(ImageSizeEnum::MEDIUM)
            ->defaultResize(200, 100)
            ->nonOptimized();
    }
}
