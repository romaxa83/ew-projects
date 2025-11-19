<?php

declare(strict_types=1);

namespace Wezom\Core\Contracts;

use Wezom\Core\Media\ImageConversionsCollection;

interface ImageConversionInterface
{
    public function register(): ImageConversionsCollection;
}
