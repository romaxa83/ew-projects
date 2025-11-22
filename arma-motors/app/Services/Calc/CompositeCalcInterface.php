<?php

namespace App\Services\Calc;

interface CompositeCalcInterface
{
    public function setChildItem(CompositeItemCalcInterface $item);
}
