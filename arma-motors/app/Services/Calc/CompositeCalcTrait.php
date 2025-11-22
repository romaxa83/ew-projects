<?php

namespace App\Services\Calc;

trait CompositeCalcTrait
{
    private $compositeItems = [];

    public function setChildItem(CompositeItemCalcInterface $item)
    {
        $this->compositeItems[] = $item;
    }

    public function clearChildItem()
    {
        $this->compositeItems = [];
    }
}
