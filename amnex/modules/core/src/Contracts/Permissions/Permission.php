<?php

declare(strict_types=1);

namespace Wezom\Core\Contracts\Permissions;

use Illuminate\Contracts\Support\Arrayable;

interface Permission extends Arrayable
{
    public function getKey(): string;

    public function getName(): string;

    public function getPosition(): int;
}
