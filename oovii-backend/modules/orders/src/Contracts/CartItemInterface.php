<?php

namespace WezomCms\Orders\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use WezomCms\Orders\Models\CartItem as CartItemModel;

interface CartItemInterface extends Arrayable, Jsonable
{
    /**
     * CartItemInterface constructor.
     * @param  CartInterface  $cart
     * @param  PurchasedProductInterface  $product
     * @param  float  $quantity
     * @param  array  $options
     */
    public function __construct(
        CartInterface $cart,
        PurchasedProductInterface $product,
        float $quantity,
        array $options = []
    );

    /**
     * @param  CartInterface  $cart
     * @return CartItemInterface
     */
    public function setCart(CartInterface $cart): CartItemInterface;

    /**
     * @return string
     */
    public function getUniqueId(): string;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * Get sub total price. Without applied discount, promo etc.
     *
     * @return float|int
     */
    public function getSubTotal();

    /**
     * Get crossed out sub total price. Without applied discount, promo etc.
     *
     * @return float|int
     */
    public function crossedOutSubTotal();

    /**
     * @return float|int
     */
    public function getTotal();

    /**
     * Get total discounted price.
     *
     * @return float|int
     */
    public function totalDiscounted();

    /**
     * @param  string  $condition
     * @return float|int|null
     */
    public function discountedByCondition(string $condition);

    /**
     * @return Collection
     */
    public function getAppliedConditions(): Collection;

    /**
     * @param  string  $conditionName
     * @return CartItemConditionInterface|null
     */
    public function getAppliedCondition(string $conditionName): ?CartItemConditionInterface;

    /**
     * @param  string  $conditionName
     * @return bool
     */
    public function isAppliedCondition(string $conditionName): bool;

    /**
     * @param  float  $quantity
     * @return CartItemInterface
     */
    public function setQuantity(float $quantity): CartItemInterface;

    /**
     * @return float
     */
    public function getQuantity(): float;

    /**
     * @return Collection
     */
    public function getOptions(): Collection;

    /**
     * @return PurchasedProductInterface|mixed
     */
    public function getPurchaseItem(): PurchasedProductInterface;

    /**
     * @return bool
     */
    public function validate(): bool;

    public static function fromModel(CartInterface $cart, CartItemModel $cartItem): ?CartItemInterface;

    public function getWeight(): int;

    public function getLength(): int;

    public function getWidth(): int;

    public function getHeight(): int;
}
