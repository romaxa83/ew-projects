<?php

namespace WezomCms\Orders\Contracts;

interface CartItemConditionInterface extends PriorityConditionInterface
{
    /**
     * @param  CartItemInterface  $cartItem
     * @param  float  $price
     * @return float
     */
    public function apply(CartItemInterface $cartItem, float $price): float;

    /**
     * @param  CartItemInterface  $cartItem
     * @return bool
     */
    public function isApplicable(CartItemInterface $cartItem): bool;

    /**
     * Validate condition before adding to cart condition storage.
     *
     * @return bool
     */
    public function valid(): bool;
}
