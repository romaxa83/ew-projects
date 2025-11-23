<?php

namespace WezomCms\Orders\Cart;

use Illuminate\Support\Collection;
use WezomCms\Catalog\Models\Product;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemConditionInterface;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Contracts\PurchasedProductInterface;
use WezomCms\Orders\Models\CartItem as CartItemModel;

class CartItem implements CartItemInterface
{
    /**
     * @var string
     */
    private $uniqueId;
    /**
     * @var int
     */
    private $id;
    /**
     * @var float
     */
    private $quantity;
    /**
     * @var Collection
     */
    private $options;
    /**
     * @var PurchasedProductInterface|null
     */
    private $purchaseItem;
    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * CartItemInterface constructor.
     * @param  CartInterface $cart
     * @param  PurchasedProductInterface $product
     * @param  float  $quantity
     * @param  array  $options
     */
    public function __construct(
        CartInterface $cart,
        PurchasedProductInterface $product,
        float $quantity,
        array $options = []
    ) {
        $this->id = $product->id;
        $this->purchaseItem = $product;
        $this->quantity = $quantity;
        $this->options = collect($options);

        $this->uniqueId = $this->generateUniqueId();

        $this->cart = $cart;

        $this->loadPurchaseItem();
    }

    /**
     * @param  CartInterface  $cart
     * @return CartItemInterface
     */
    public function setCart(CartInterface $cart): CartItemInterface
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get sub total price. Without applied discount, promo etc.
     *
     * @return float|string
     */
    public function getSubTotal()
    {
        return $this->round($this->getQuantity() * $this->purchaseItem->priceForPurchase());
    }

    /**
     * Get crossed out sub total price. Without services, applied discount, promo etc.
     *
     * @return float|int
     */
    public function crossedOutSubTotal()
    {
        $price = $this->purchaseItem->oldPriceForPurchase() ?: $this->purchaseItem->priceForPurchase();

        return $this->round($this->getQuantity() * $price);
    }

    /**
     * @return float|string
     */
    public function getTotal()
    {
        $price = $this->getSubTotal();

        // If enabled cart conditions
        if (method_exists($this->cart, 'getItemConditions')) {
            foreach ($this->cart->getItemConditions() as $condition) {
                /** @var CartItemConditionInterface $condition */
                if ($condition->isApplicable($this)) {
                    $price = $condition->apply($this, $price);
                }
            }
        }

        return $price;
    }

    /**
     * Get total discounted price.
     *
     * @return float|string
     */
    public function totalDiscounted()
    {
        return $this->round($this->getSubTotal() - $this->getTotal());
    }

    /**
     * @param  string  $condition
     * @return float|int|null
     */
    public function discountedByCondition(string $condition)
    {
        if (!$this->isAppliedCondition($condition)) {
            return 0;
        }

        $condition = $this->getAppliedCondition($condition);

        if (!$condition->isApplicable($this)) {
            return 0;
        }

        $price = $this->getSubTotal();

        $discountedPrice = $condition->apply($this, $price);

        return $this->round($price - $discountedPrice);
    }

    /**
     * @return Collection
     */
    public function getAppliedConditions(): Collection
    {
        if (method_exists($this->cart, 'getItemConditions')) {
            return $this->cart->getItemConditions()
                ->filter(function (CartItemConditionInterface $condition) {
                    return $condition->isApplicable($this);
                });
        }

        return collect();
    }

    /**
     * @param  string  $conditionName
     * @return CartItemConditionInterface|null
     */
    public function getAppliedCondition(string $conditionName): ?CartItemConditionInterface
    {
        $appliedConditions = $this->getAppliedConditions();

        $index = $appliedConditions->search(function (CartItemConditionInterface $condition) use ($conditionName) {
            return $condition instanceof $conditionName;
        });

        return $appliedConditions->get($index);
    }

    /**
     * @param  string  $conditionName
     * @return bool
     */
    public function isAppliedCondition(string $conditionName): bool
    {
        return $this->getAppliedConditions()
            ->filter(function (CartItemConditionInterface $condition) use ($conditionName) {
                return $condition instanceof $conditionName;
            })
            ->isNotEmpty();
    }

    /**
     * @param  float  $quantity
     * @return CartItemInterface
     */
    public function setQuantity(float $quantity): CartItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return round($this->quantity, $this->cart->getQuantityPrecision());
    }

    /**
     * @return Collection
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * @return PurchasedProductInterface|Product|mixed
     */
    public function getPurchaseItem(): PurchasedProductInterface
    {
        return $this->purchaseItem;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return $this->purchaseItem !== null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'unique_id' => $this->getUniqueId(),
            'id' => $this->getId(),
            'quantity' => $this->getQuantity(),
            'options' => $this->getOptions(),
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array_filter(array_keys(get_object_vars($this)), function ($key) {
            return !in_array($key, ['cart', 'purchaseItem']);
        });
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        $this->loadPurchaseItem();
    }

    /**
     * @return string
     */
    protected function generateUniqueId()
    {
        return md5($this->id . serialize($this->options->sortKeys()));
    }

    /**
     * @param $value
     * @return float
     */
    protected function round($value): float
    {
        return round($value, $this->cart->getPrecision());
    }

    protected function loadPurchaseItem()
    {
        // $this->purchaseItem = Product::published()->find($this->getId());
    }

    public static function fromModel(CartInterface $cart, CartItemModel $cartItem): ?CartItemInterface
    {
        $product = $cartItem->getProduct();

        if (!$product) {
            return null;
        }

        return new self(
            $cart,
            $product,
            $cartItem->getContentQuantity(),
            $cartItem->getContentOptions()
        );
    }

    public function getWeight(): int
    {
        return $this->getPurchaseItem()->getWeight() * $this->getQuantity();
    }

    public function getLength(): int
    {
        return $this->getPurchaseItem()->getLength() * $this->getQuantity();
    }

    public function getWidth(): int
    {
        return $this->getPurchaseItem()->getWidth();
    }

    public function getHeight(): int
    {
        return $this->getPurchaseItem()->getHeight();
    }
}
