<?php

namespace WezomCms\Orders\Contracts;

interface CartConditionInterface extends PriorityConditionInterface
{
    /**
     * @param  CartInterface  $cart
     * @param  float  $price
     * @return float
     */
    public function apply(CartInterface $cart, float $price): float;

    /**
     * Validate condition before adding to cart condition storage.
     *
     * @return bool
     */
    public function valid(): bool;
}
