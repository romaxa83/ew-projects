<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ActiveTrait
{
    public function isActive(): bool
    {
        $this->checkField();

        return $this->active;
    }

    public function toggleActive()
    {
        $this->checkField();

        $this->active = !$this->active;
        $this->save();
    }

    public function scopeActive(Builder $query)
    {
        $this->checkField();

        return $query->where('active', true);
    }

    private function checkField()
    {
//        dd(in_array('active', $this->getFillable()));
        if(!(null !== $this->getAttributeValue('active') || key_exists('active', $this->getCasts()))){
            throw new \Exception("Don't have \"active\" field in the model");
        }
    }
}
