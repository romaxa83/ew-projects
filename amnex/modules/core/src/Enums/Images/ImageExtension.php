<?php

declare(strict_types=1);

namespace Wezom\Core\Enums\Images;

use Wezom\Core\Contracts\Extensions\Extension;

enum ImageExtension: string implements Extension
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';

    public function get(): string
    {
        return $this->value;
    }
}
