<?php

declare(strict_types=1);

namespace Wezom\Core\Entities;

readonly class SelectOption
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $hasChildren,
        public bool $disabled,
        public int $depth
    ) {
    }
}
