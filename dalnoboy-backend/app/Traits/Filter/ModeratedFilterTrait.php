<?php

namespace App\Traits\Filter;

trait ModeratedFilterTrait
{
    public function isModerated(bool $isModerated): void
    {
        $this->where('is_moderated', $isModerated);
    }
}
