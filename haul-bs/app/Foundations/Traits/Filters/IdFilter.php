<?php

namespace App\Foundations\Traits\Filters;

trait IdFilter
{
    public function id(int|string|array $id): void
    {
        if(is_array($id)){
            $this->ids($id);
            return;
        }
        $this->where('id', $id);
    }

    public function ids(array $ids): void
    {
        $this->whereIn('id', $ids);
    }

    public function without(int $id): void
    {
        $this->whereNot('id', $id);
    }

    public function withoutIds(array $ids): void
    {
        $this->whereNotIn('id', $ids);
    }
}

