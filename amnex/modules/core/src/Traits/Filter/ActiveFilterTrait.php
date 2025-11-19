<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Filter;

trait ActiveFilterTrait
{
    public function active(bool $value): void
    {
        $this->where($this->query->getModel()->getTable() . '.active', $value);
    }
}
