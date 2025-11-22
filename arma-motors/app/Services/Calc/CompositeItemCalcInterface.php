<?php

namespace App\Services\Calc;

// каждый элемент в цепочке должен реализовать данный интерфейс
interface CompositeItemCalcInterface
{
    // просчет обычной цены
    public function calcPrice(null|float $price = null): float;
    // просчет акционой цены
    public function calcPriceDiscount(null|float $price = null): null|float;
    // название позиции
    public function name(): string;
    // кол-во или коэф.
    public function qty(): string;
    // единица измерения.
    public function unit(): string;
}
