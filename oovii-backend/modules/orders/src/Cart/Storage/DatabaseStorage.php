<?php

namespace WezomCms\Orders\Cart\Storage;

use Exception;
use WezomCms\Orders\Cart\CartItem as CartItemWrapper;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Contracts\NeedClearOldHashesInterface;
use WezomCms\Orders\Models\Cart;
use WezomCms\Orders\Models\CartItem;

class DatabaseStorage extends AbstractStorage implements NeedClearOldHashesInterface
{
    /**
     * @var Cart
     */
    private $mainCart;

    private bool $runningInConsole;

    /**
     * DatabaseStorage constructor.
     * @param int $precision
     * @param int $quantityPrecision
     */
    public function __construct(int $precision = 0, int $quantityPrecision = 0)
    {
        parent::__construct($precision, $quantityPrecision);

        $this->runningInConsole = app()->runningInConsole();

        $this->restoreCart();

        /*if ($this->runningInConsole) {
            $this->mainCart = new Cart();
        } else {
            $this->restoreCart();
        }*/
    }

    /**
     * Load cart and cart items from database.
     */
    protected function restoreCart(): void
    {
        $this->mainCart = Cart::firstOrCreate(['hash' => $this->makeHash()]);

        $this->items = $this->mainCart
            ->items
            ->load('product')
            ->mapWithKeys(function (CartItem $cartItem) {
                return [
                    $cartItem->unique_id => CartItemWrapper::fromModel($this, $cartItem)
                ];
            })
            ->filter(function (CartItemWrapper $cartItem) {
                return (bool)$cartItem->getPurchaseItem()
                    && $cartItem->getPurchaseItem()->availableForPurchase();
            });

        foreach ((array) $this->mainCart->conditions as $condition) {
            $this->applyCondition(unserialize($condition));
        }
    }

    /**
     * @param  CartItemInterface  $item
     * @return bool
     */
    public function insert(CartItemInterface $item): bool
    {
        $uniqueId = $item->getUniqueId();

        if ($this->has($uniqueId)) {
            $row = $this->mainCart->items()->where('unique_id', $uniqueId)->first();

            if ($row) {
                $row->content = $item;

                $result = $row->save();
            }
        } else {
            $row = new CartItem();
            $row->unique_id = $uniqueId;
            $row->cart()->associate($this->mainCart);
            $row->content = $item;

            $result = $row->save();
        }

        if (isset($result) && $result) {
            $this->items->put($uniqueId, $item);
        }

        $this->clearDeliveryData();

        return $result ?? false;
    }

    /**
     * @param  string  $uniqueId
     * @return bool
     */
    public function remove(string $uniqueId): bool
    {
        $this->mainCart->items()->where('unique_id', $uniqueId)->delete();
        if ($this->items->has($uniqueId)) {
            $this->items->forget($uniqueId);
        }

        $this->clearDeliveryData();

        return parent::remove($uniqueId);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        try {
            $this->mainCart->items()->delete();
        } catch (Exception $e) {
            logger($e->getMessage());

            return false;
        }

        $this->items = collect();

        $this->clearDeliveryData();

        $this->clearConditions();

        return true;
    }

    /**
     * @return bool
     */
    public function clearOldHashes(): bool
    {
        try {
            Cart::where('created_at', '<', now()->subDay())
                ->whereDoesntHave('items')
                ->delete();
        } catch (Exception $e) {
            logger($e->getMessage());

            return false;
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->runningInConsole) {
            return;
        }

        $conditions = $this->getConditions()
            ->map(function ($condition) {
                return serialize($condition);
            })->toArray();

        $this->mainCart->conditions = $conditions;
        $this->mainCart->save();

        $this->items->map(function (CartItemInterface $cartItem) {
            $this->mainCart->items()
                ->where('unique_id', $cartItem->getUniqueId())
                ->update(['content' => $cartItem->toJson()]);
        });
    }

    public function getMainCart(): Cart
    {
        return $this->mainCart;
    }

    public function setDeliveryData(array $deliveryData): void
    {
        parent::setDeliveryData($deliveryData);

        $this->mainCart->delivery_data = $deliveryData;
    }

    public function clearDeliveryData(): void
    {
        parent::clearDeliveryData();

        $this->mainCart->delivery_data = [];
    }

    public function getDeliveryCost(int $tariffCode, int $providerId): float
    {
        return data_get(
            $this->mainCart->delivery_data,
            $tariffCode . '.' . $providerId,
            0.0
        );
    }
}
