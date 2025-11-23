<?php

namespace WezomCms\Orders\Conditions;

use Auth;
use WezomCms\Orders\Contracts\CartConditionInterface;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Traits\PriorityConditionTrait;
use WezomCms\Users\Models\User;

class BonusCondition implements CartConditionInterface
{
    use PriorityConditionTrait;

    private int $countUsedBonuses = 0;

    private float $cartTotal;

    private User $user;

    /**
     * BonusCondition constructor.
     * @param int $countUsedBonuses
     * @param float $cartTotal
     */
    public function __construct(int $countUsedBonuses, float $cartTotal)
    {
        $this->user = Auth::user();
        $this->cartTotal = $cartTotal;
        $this->setUsedBonuses(abs($countUsedBonuses));
        $this->setPriority(1);
    }

    /**
     * @param  CartInterface  $cart
     * @param  float  $price
     * @return float
     */
    public function apply(CartInterface $cart, float $price): float
    {
        if ($this->valid()) {
            return $price - $this->countUsedBonuses;
        }

        return $price;
    }

    public function setUsedBonuses(int $countUsedBonuses): void
    {
        $maxAvailableForUsage = $this->user->bonus;
        $this->countUsedBonuses = min($countUsedBonuses, $maxAvailableForUsage);

        if ($countUsedBonuses >= $this->cartTotal) {
            $this->countUsedBonuses = $this->cartTotal;
        }
    }

    public function getUsedBonuses(): int
    {
        return $this->countUsedBonuses;
    }

    public function valid(): bool
    {
        return $this->user && $this->user->canUseBonuses();
    }
}
