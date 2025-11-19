<?php

declare(strict_types=1);

namespace Wezom\Core\Contracts\Extensions;

interface Extension
{
    public function get(): string;
}
