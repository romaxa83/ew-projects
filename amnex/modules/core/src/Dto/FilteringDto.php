<?php

declare(strict_types=1);

namespace Wezom\Core\Dto;

readonly class FilteringDto
{
    public function __construct(private array $filtering, private array $ordering)
    {
    }

    public function getFilters(): array
    {
        return array_merge(['ordering' => $this->ordering], $this->filtering);
    }
}
