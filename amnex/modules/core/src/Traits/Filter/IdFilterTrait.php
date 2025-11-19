<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Filter;

trait IdFilterTrait
{
    public function id(int $id): void
    {
        $this->whereKey($id);
    }

    public function ids(array $ids): void
    {
        $this->whereKey($ids);
    }
}
