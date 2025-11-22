<?php

namespace App\Traits\Filter;

trait SlugFilterTrait
{
    public function slug(string $slug): void
    {
        $this->where(
            $this->getModel()
                ->getTable() . '.slug',
            $slug
        );
    }

    public function slugs(array $slugs): void
    {
        $this->whereIn(
            $this->getModel()
                ->getTable() . '.slug',
            $slugs
        );
    }
}
