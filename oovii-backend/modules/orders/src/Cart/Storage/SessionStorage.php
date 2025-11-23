<?php

namespace WezomCms\Orders\Cart\Storage;

use Illuminate\Session\SessionManager;
use InvalidArgumentException;
use WezomCms\Orders\Cart\CartItem;
use WezomCms\Orders\Contracts\CartConditionInterface;
use WezomCms\Orders\Contracts\CartItemConditionInterface;
use WezomCms\Orders\Contracts\CartItemInterface;

class SessionStorage extends AbstractStorage
{
    /**
     * @var SessionManager
     */
    private $session;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $conditionsKey;

    /**
     * StorageInterface constructor.
     * @param  int  $precision
     * @param  int  $quantityPrecision
     */
    public function __construct(int $precision = 0, int $quantityPrecision = 0)
    {
        parent::__construct($precision, $quantityPrecision);

        $this->session = app('session');

        $this->key = 'cart-' . $this->makeHash();

        $this->conditionsKey = $this->key . '-conditions';

        $this->items = $this->session->get($this->key, collect())
            ->map(function (CartItem $cartItem) {
                return $cartItem->setCart($this);
            });

        collect($this->session->get($this->conditionsKey, []))
            ->each(function ($condition) {
                $this->applyCondition(unserialize($condition));
            });
    }

    /**
     * @param  CartItemInterface  $item
     * @return bool
     */
    public function insert(CartItemInterface $item): bool
    {
        $this->items->put($item->getUniqueId(), $item);

        $this->updateSession();

        return true;
    }

    /**
     * @param  string  $uniqueId
     * @return bool
     */
    public function remove(string $uniqueId): bool
    {
        $this->items->forget($uniqueId);

        $this->updateSession();

        return parent::remove($uniqueId);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $this->items = collect();

        $this->clearConditions();

        $this->updateSession();

        return true;
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     * @return bool
     * @throws InvalidArgumentException
     */
    public function applyCondition($condition): bool
    {
        $result = parent::applyCondition($condition);

        if ($result) {
            $this->updateSession();
        }

        return $result;
    }

    /**
     * @param  CartConditionInterface|CartItemConditionInterface  $condition
     * @return bool
     */
    public function removeCondition($condition): bool
    {
        $result = parent::removeCondition($condition);

        if ($result) {
            $this->updateSession();
        }

        return $result;
    }

    /**
     * Save items & conditions in session storage.
     */
    protected function updateSession()
    {
        $this->session->put($this->key, $this->items);

        $conditions = collect($this->getConditions())->map(function ($condition) {
            return serialize($condition);
        })->toArray();

        $this->session->put($this->conditionsKey, $conditions);
    }
}
