<?php

namespace WezomCms\Orders\Cart\Storage;

use Cookie;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use WezomCms\Orders\Cart\CartItem;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Contracts\PurchasedProductInterface;
use WezomCms\Orders\Traits\CartConditionsTrait;

abstract class AbstractStorage implements CartInterface
{
    use CartConditionsTrait;

    /**
     * @var int
     */
    protected $precision;

    /**
     * @var int
     */
    protected $quantityPrecision;

    /**
     * @var \Illuminate\Support\Collection|CartItemInterface[]
     */
    protected $items;

    /**
     * @var null|array
     */
    protected $productIds;

    protected array $deliveryData = [];

    /**
     * AbstractStorage constructor.
     * @param  int  $precision
     * @param  int  $quantityPrecision
     */
    public function __construct(int $precision = 0, int $quantityPrecision = 0)
    {
        $this->precision = $precision;
        $this->quantityPrecision = $quantityPrecision;

        $this->items = collect();
    }

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
    ): CartItemInterface {
        $cartItem = new CartItem($this, $product, $quantity, $options);

        $uniqueId = $cartItem->getUniqueId();

        if ($this->has($uniqueId)) {
            $newQuantity = $cartItem->getQuantity() + $this->get($uniqueId)->getQuantity();

            $cartItem->setQuantity($newQuantity);
        }

        $this->insert($cartItem);

        $this->forgetProductIds();

        return $cartItem;
    }

    /**
     * @param  array|\Illuminate\Support\Collection|iterable  $items
     * @return CartInterface
     */
    public function massAssignment($items): CartInterface
    {
        collect($items)->filter(function ($item) {
            return $item instanceof PurchasedProductInterface;
        })->each(function ($item) {
            $this->add($item->id, $item->minCountForPurchase());
        });

        return $this;
    }

    /**
     * @param  string  $uniqueId
     * @return boolean
     */
    public function has(string $uniqueId): bool
    {
        return $this->items->has($uniqueId);
    }

    /**
     * @param $uniqueId
     * @return CartItemInterface|null
     */
    public function get(string $uniqueId): ?CartItemInterface
    {
        return $this->items->get($uniqueId);
    }

    /**
     * Check if cart has product.
     *
     * @param  int  $id
     * @return bool
     */
    public function hasProduct(int $id): bool
    {
        return in_array($id, $this->getProductIds(), true);
    }

    public function getProductIds(): array
    {
        if (null === $this->productIds) {
            $this->productIds = $this->items
                ->map(function (CartItemInterface $cartItem) {
                    return $cartItem->getId();
                })
                ->values()
                ->all();
        }

        return $this->productIds;
    }

    public function productQuantity(int $id): float
    {
        /** @var CartItemInterface $item */
        $item = $this->items
            ->first(fn (CartItemInterface $item) => $item->getId() === $id);

        return $item ? $item->getQuantity() : 0.0;
    }

    /**
     * @param  string  $uniqueId
     * @return bool
     */
    public function remove(string $uniqueId): bool
    {
        $this->forgetProductIds();

        return true;
    }

    /**
     * Get shopping cart content.
     *
     * @return CartItemInterface[]|\Illuminate\Support\Collection
     */
    public function content(): Collection
    {
        return $this->items;
    }

    public function separatedContent(): Collection
    {
        return $this->content()
            ->groupBy(function (CartItem $cartItem) {
                return $cartItem->getPurchaseItem()->provider_id;
            });
    }

    /**
     * Set the quantity of the cart item with specified unique id.
     *
     * @param  string  $uniqueId
     * @param  float  $quantity
     * @return bool
     */
    public function setQuantity(string $uniqueId, float $quantity): bool
    {
        if (($cartItem = $this->items->get($uniqueId)) !== null) {
            $cartItem->setQuantity($quantity);

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Get sub total price. Without applied discount, promo etc.
     *
     * @return float|string
     */
    public function subTotal()
    {
        return $this->round(
            $this->items->sum(function (CartItemInterface $cartItem) {
                return $cartItem->getSubTotal();
            })
        );
    }

    /**
     * Get crossed out sub total price. Without services, applied discount, promo etc.
     *
     * @return float|int
     */
    public function crossedOutSubTotal()
    {
        return $this->round(
            $this->items->sum(function (CartItemInterface $cartItem) {
                return $cartItem->crossedOutSubTotal();
            })
        );
    }

    /**
     * Get total price.
     *
     * @return float|string
     */
    public function total()
    {
        $price = $this->items->sum(function (CartItemInterface $cartItem) {
            return $cartItem->getTotal();
        });

        if (method_exists($this, 'getCartConditions')) {
            foreach ($this->getCartConditions() as $condition) {
                $price = $condition->apply($this, $price);
            }
        }

        return $this->round($price);
    }

    /**
     * Get total discount price.
     *
     * @return float|string
     */
    public function discounted()
    {
        return $this->round(
            $this->items->sum(function (CartItemInterface $cartItem) {
                return $cartItem->totalDiscounted();
            })
        );
    }

    /**
     * @param  string  $condition
     * @return float|int|null
     */
    public function discountedByCondition(string $condition)
    {
        return $this->round(
            $this->items->sum(function (CartItemInterface $cartItem) use ($condition) {
                return $cartItem->discountedByCondition($condition);
            })
        );
    }

    /**
     * @return float
     */
    public function quantity(): float
    {
        return $this->items->sum(function (CartItemInterface $cartItem) {
            return $cartItem->getQuantity();
        });
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->items->isNotEmpty();
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @return int
     */
    public function getQuantityPrecision(): int
    {
        return $this->quantityPrecision;
    }

    protected function makeHash(): string
    {
        $hash = config('cms.orders.cart.hash');

        if ($hash) {
            return $hash;
        }

        $hash = sha1(microtime() . Str::random());
        Cookie::queue(Cookie::make(static::COOKIE_KEY, $hash, static::COOKIE_LIFE_TIME, '/'));

        return $hash;
    }


    /**
     * @param  float  $value
     * @return float
     */
    protected function round(float $value): float
    {
        return round($value, $this->precision);
    }

    /**
     * Clear array with cached products IDs.
     */
    protected function forgetProductIds()
    {
        $this->productIds = null;
    }

    public function setDeliveryData(array $deliveryData): void
    {
        $this->deliveryData = $deliveryData;
    }

    public function clearDeliveryData(): void
    {
        $this->deliveryData = [];
    }

    public function getDeliveryCost(int $tariffCode, int $providerId): float
    {
        return 0.0;
    }
}
