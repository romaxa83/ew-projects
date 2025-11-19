<?php

namespace Wezom\Core\Annotations;

use Attribute;
use BackedEnum;
use UnitEnum;
use Wezom\Core\Contracts\ImageConversionInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ImageConversions
{
    public function __construct(
        /** @var class-string<ImageConversionInterface> */
        public string $conversions,
        public BackedEnum|UnitEnum|string|null $collection = null
    ) {
    }
}
