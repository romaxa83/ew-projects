<?php

namespace App\Traits\Filter;

trait IdFilterTrait
{
    public function id(int $id): void
    {
        $this->where(
            $this->getModel()
                ->getTable() . '.id',
            $id
        );
    }

    public function ids(array $ids): void
    {
        $this->whereIn(
            $this->getModel()
                ->getTable() . '.id',
            $ids
        );
    }
}
