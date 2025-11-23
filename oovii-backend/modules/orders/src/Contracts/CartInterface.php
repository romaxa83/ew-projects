<?php

namespace WezomCms\Orders\Contracts;

use Illuminate\Support\Collection;

interface CartInterface
{
    public const COOKIE_KEY = 'cart';
    public const COOKIE_LIFE_TIME = 525600; // 1 year

    /**
     * Add an item to the shopping cart.
     *
     * If an item is already in the shopping cart then we simply update its quantity.
     *
     * @param  PurchasedProductInterface  $product
     * @param  float  $quantity
     * @param  array  $options
     * @return CartItemInterface
     */
    public function add(
        PurchasedProductInterface $product,
        float $quantity,
        array $options = []
    ): CartItemInterface;

    /**
     * @param  array|\Illuminate\Support\Collection|iterable  $items
     * @return CartInterface
     */
    public function massAssignment($items): CartInterface;

    /**
     * @param  CartItemInterface  $item
     * @return bool
     */
    public function insert(CartItemInterface $item): bool;

    /**
     * Get the item with the specified unique id from shopping cart.
     *
     * @param  string  $uniqueId
     * @return CartItemInterface|null
     */
    public function get(string $uniqueId): ?CartItemInterface;

    /**
     * @param  string  $uniqueId
     * @return boolean
     */
    public function has(string $uniqueId): bool;

    /**
     * Check if cart has product.
     *
     * @param  int  $id
     * @return bool
     */
    public function hasProduct(int $id): bool;

    public function productQuantity(int $id): float;

    public function getProductIds(): array;

    /**
     * Remove the item with the specified unique id from shopping cart.
     *
     * @param  string  $uniqueId
     * @return bool
     */
    public function remove(string $uniqueId): bool;

    /**
     * Get shopping cart content.
     *
     * @return CartItemInterface[]|\Illuminate\Support\Collection
     */
    public function content(): Collection;

    public function separatedContent(): Collection;

    /**
     * Set the quantity of the cart item with specified unique id.
     *
     * @param  string  $uniqueId
     * @param  float  $quantity
     * @return bool
     */
    public function setQuantity(string $uniqueId, float $quantity): bool;

    /**
     * Clear shopping cart.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Get the number of item in the shopping cart.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get the total items quantity in the shopping cart.
     *
     * @return float
     */
    public function quantity(): float;

    /**
     * Get sub total price. Without applied discount, promo etc.
     *
     * @return float|int
     */
    public function subTotal();

    /**
     * Get crossed out sub total price. Without services, applied discount, promo etc.
     *
     * @return float|int
     */
    public function crossedOutSubTotal();

    /**
     * Get total price.
     *
     * @return float|int
     */
    public function total();

    /**
     * Get total discounted price.
     *
     * @return float|int
     */
    public function discounted();

    /**
     * @param  string  $condition
     * @return float|int|null
     */
    public function discountedByCondition(string $condition);

    /**
     * @param  string  $conditionName
     * @return bool
     */
    public function isAppliedCondition(string $conditionName): bool;

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     *
     * @return void
     */
    public static function addGlobalCondition($condition): void;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * @return int
     */
    public function getPrecision(): int;

    /**
     * @return int
     */
    public function getQuantityPrecision(): int;

    public function setDeliveryData(array $deliveryData): void;

    public function clearDeliveryData(): void;

    public function getDeliveryCost(int $tariffCode, int $providerId): float;
}
